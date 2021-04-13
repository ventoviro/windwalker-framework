<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Compiler\Concern;

/**
 * Trait CompileEchosTrait
 */
trait CompileEchoTrait
{
    /**
     * Compile Blade echos into valid PHP.
     *
     * @param  string  $value
     *
     * @return string
     */
    public function compileEchos(string $value): string
    {
        foreach ($this->getEchoMethods() as $method => $length) {
            $value = $this->$method($value);
        }

        return $value;
    }

    /**
     * Compile the "raw" echo statements.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function compileRawEchos(string $value): string
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

            return $matches[1] ? substr(
                $matches[0],
                1
            ) : '<?php echo ' . $this->compileEchoDefaults($matches[2]) . '; ?>' . $whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the "regular" echo statements.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function compileRegularEchos(string $value): string
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

            $wrapped = sprintf($this->echoFormat, $this->compileEchoDefaults($matches[2]));

            return $matches[1] ? substr($matches[0], 1) : '<?php echo ' . $wrapped . '; ?>' . $whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the escaped echo statements.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function compileEscapedEchos(string $value): string
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

            return $matches[1]
                ? $matches[0]
                : '<?php echo e(' . $this->compileEchoDefaults(
                    $matches[2]
                ) . '); ?>' . $whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the default values for the echo statement.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function compileEchoDefaults(string $value): string
    {
        return preg_replace('/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s', 'isset($1) ? $1 : $2', $value);
    }

    /**
     * Get the echo methods in the proper order for compilation.
     *
     * @return array
     */
    protected function getEchoMethods(): array
    {
        $methods = [
            'compileRawEchos' => strlen(stripcslashes($this->rawTags[0])),
            'compileEscapedEchos' => strlen(stripcslashes($this->escapedTags[0])),
            'compileRegularEchos' => strlen(stripcslashes($this->contentTags[0])),
        ];

        uksort(
            $methods,
            static function ($method1, $method2) use ($methods) {
                // Ensure the longest tags are processed first
                if ($methods[$method1] > $methods[$method2]) {
                    return -1;
                }

                if ($methods[$method1] < $methods[$method2]) {
                    return 1;
                }

                // Otherwise give preference to raw tags (assuming they've overridden)
                if ($method1 === 'compileRawEchos') {
                    return -1;
                }

                if ($method2 === 'compileRawEchos') {
                    return 1;
                }

                if ($method1 === 'compileEscapedEchos') {
                    return -1;
                }

                if ($method2 === 'compileEscapedEchos') {
                    return 1;
                }

                return null;
            }
        );

        return $methods;
    }
}
