<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Compiler;

use Windwalker\Edge\Compiler\Concern\CompileCommentTrait;
use Windwalker\Edge\Compiler\Concern\CompileComponentTrait;
use Windwalker\Edge\Compiler\Concern\CompileConditional;
use Windwalker\Edge\Compiler\Concern\CompileEchoTrait;
use Windwalker\Edge\Compiler\Concern\CompileIncludeTrait;
use Windwalker\Edge\Compiler\Concern\CompileLayoutTrait;
use Windwalker\Edge\Compiler\Concern\CompileLoopTrait;
use Windwalker\Edge\Compiler\Concern\CompileRawPhpTrait;
use Windwalker\Edge\Compiler\Concern\CompileStackTrait;

/**
 * The EdgeCompiler class.
 *
 * This is a modified version of Laravel Blade engine.
 *
 * @see    https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php
 *
 * @since  3.0
 */
class EdgeCompiler implements EdgeCompilerInterface
{
    use CompileCommentTrait;
    use CompileComponentTrait;
    use CompileConditional;
    use CompileEchoTrait;
    use CompileIncludeTrait;
    use CompileLayoutTrait;
    use CompileLoopTrait;
    use CompileRawPhpTrait;
    use CompileStackTrait;

    /**
     * All custom "directive" handlers.
     *
     * @var  callable[]
     */
    protected array $directives = [];

    /**
     * Property parsers.
     *
     * @var  callable[]
     */
    protected array $parsers = [];

    /**
     * The file currently being compiled.
     *
     * @var string
     */
    protected string $path;

    /**
     * Array of opening and closing tags for raw echos.
     *
     * @var array
     */
    protected array $rawTags = ['{!!', '!!}'];

    /**
     * Array of opening and closing tags for regular echos.
     *
     * @var array
     */
    protected array $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    protected array $escapedTags = ['{{{', '}}}'];

    /**
     * The "regular" / legacy echo string format.
     *
     * @var string
     */
    protected string $echoFormat = '$__edge->escape(%s)';

    /**
     * Array of footer lines to be added to template.
     *
     * @var array
     */
    protected array $footer = [];

    /**
     * Placeholder to temporary mark the position of verbatim blocks.
     *
     * @var string
     */
    protected string $verbatimPlaceholder = '@__verbatim__@';

    /**
     * Array to temporary store the verbatim blocks found in the template.
     *
     * @var array
     */
    protected array $verbatimBlocks = [];

    /**
     * Counter to keep track of nested forelse statements.
     *
     * @var int
     */
    protected int $forelseCounter = 0;

    /**
     * All of the available compiler functions.
     *
     * @var array
     */
    protected array $compilers = [
        'Parsers',
        'Statements',
        'Comments',
        'Echos',
    ];

    /**
     * compile
     *
     * @param  string  $value
     *
     * @return  string
     */
    public function compile(string $value): string
    {
        $result = '';

        if (str_contains($value, '@verbatim')) {
            $value = $this->storeVerbatimBlocks($value);
        }

        $this->footer = [];

        // Here we will loop through all of the tokens returned by the Zend lexer and
        // parse each one into the corresponding valid PHP. We will then have this
        // template as the correctly rendered PHP that can be rendered natively.
        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        if (!empty($this->verbatimBlocks)) {
            $result = $this->restoreVerbatimBlocks($result);
        }

        // If there are any footer lines that need to get added to a template we will
        // add them here at the end of the template. This gets used mainly for the
        // template inheritance via the extends keyword that should be appended.
        if (count($this->footer) > 0) {
            $result = ltrim($result, PHP_EOL)
                . PHP_EOL . implode(PHP_EOL, array_reverse($this->footer));
        }

        return $result;
    }

    /**
     * Store the verbatim blocks and replace them with a temporary placeholder.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function storeVerbatimBlocks(string $value): string
    {
        return preg_replace_callback(
            '/(?<!@)@verbatim(.*?)@endverbatim/s',
            function ($matches) {
                $this->verbatimBlocks[] = $matches[1];

                return $this->verbatimPlaceholder;
            },
            $value
        );
    }

    /**
     * Replace the raw placeholders with the original code stored in the raw blocks.
     *
     * @param  string  $result
     *
     * @return string
     */
    protected function restoreVerbatimBlocks(string $result): string
    {
        $result = preg_replace_callback(
            '/' . preg_quote($this->verbatimPlaceholder) . '/',
            function () {
                return array_shift($this->verbatimBlocks);
            },
            $result
        );

        $this->verbatimBlocks = [];

        return $result;
    }

    /**
     * Parse the tokens from the template.
     *
     * @param  array  $token
     *
     * @return string
     */
    protected function parseToken(array $token): string
    {
        [$id, $content] = $token;

        if ($id === T_INLINE_HTML) {
            foreach ($this->compilers as $type) {
                $content = $this->{"compile{$type}"}($content);
            }
        }

        return $content;
    }

    /**
     * compileParsers
     *
     * @param  string  $value
     *
     * @return  string
     */
    protected function compileParsers(string $value): string
    {
        foreach ($this->parsers as $parser) {
            $value = $parser($value, $this);
        }

        return $value;
    }

    /**
     * Compile Blade statements that start with "@".
     *
     * @param  string  $value
     *
     * @return mixed
     */
    protected function compileStatements(string $value): mixed
    {
        return preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
            [$this, 'compileStatement'],
            $value
        );
    }

    /**
     * compileStatement
     *
     * @param  array  $match
     *
     * @return  string
     */
    protected function compileStatement(array $match): string
    {
        if (str_contains($match[1], '@')) {
            $match[0] = isset($match[3]) ? $match[1] . $match[3] : $match[1];
        } elseif (isset($this->directives[$match[1]])) {
            $match[0] = call_user_func($this->directives[$match[1]], $match[3] ?? '');
        } elseif (method_exists($this, $method = 'compile' . ucfirst($match[1]))) {
            $match[0] = $this->$method($match[3] ?? '');
        }

        return isset($match[3]) ? $match[0] : $match[0] . $match[2];
    }

    /**
     * Strip the parentheses from the given expression.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function stripParentheses(string $expression): string
    {
        if (str_starts_with($expression, '(')) {
            $expression = substr($expression, 1, -1);
        }

        return $expression;
    }

    /**
     * Register a handler for custom directives.
     *
     * @param  string    $name
     * @param  callable  $handler
     *
     * @return EdgeCompiler
     */
    public function directive(string $name, callable $handler): static
    {
        $this->directives[$name] = $handler;

        return $this;
    }

    /**
     * Get the list of custom directives.
     *
     * @return array
     */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /**
     * Method to set property directives
     *
     * @param  callable[]  $directives
     *
     * @return  static  Return self to support chaining.
     */
    public function setDirectives(array $directives): static
    {
        $this->directives = $directives;

        return $this;
    }

    /**
     * parser
     *
     * @param  callable  $handler
     *
     * @return static
     */
    public function parser(callable $handler): static
    {
        $this->parsers[] = $handler;

        return $this;
    }

    /**
     * Method to set property parsers
     *
     * @param  callable[]  $parsers
     *
     * @return  static  Return self to support chaining.
     */
    public function setParsers(array $parsers): static
    {
        $this->parsers = $parsers;

        return $this;
    }

    /**
     * getParsers
     *
     * @return  callable[]
     */
    public function getParsers(): array
    {
        return $this->parsers;
    }

    /**
     * Gets the raw tags used by the compiler.
     *
     * @return array
     */
    public function getRawTags(): array
    {
        return $this->rawTags;
    }

    /**
     * Sets the raw tags used for the compiler.
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     *
     * @return void
     */
    public function setRawTags(string $openTag, string $closeTag): void
    {
        $this->rawTags = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Sets the content tags used for the compiler.
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     * @param  bool    $escaped
     *
     * @return void
     */
    public function setContentTags(string $openTag, string $closeTag, bool $escaped = false)
    {
        $property = ($escaped === true) ? 'escapedTags' : 'contentTags';

        $this->{$property} = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Sets the escaped content tags used for the compiler.
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     *
     * @return void
     */
    public function setEscapedContentTags(string $openTag, string $closeTag): void
    {
        $this->setContentTags($openTag, $closeTag, true);
    }

    /**
     * Gets the content tags used for the compiler.
     *
     * @return array
     */
    public function getContentTags(): array
    {
        return $this->getTags();
    }

    /**
     * Gets the escaped content tags used for the compiler.
     *
     * @return array
     */
    public function getEscapedContentTags(): array
    {
        return $this->getTags(true);
    }

    /**
     * Gets the tags used for the compiler.
     *
     * @param  bool  $escaped
     *
     * @return array
     */
    protected function getTags(bool $escaped = false): array
    {
        $tags = $escaped ? $this->escapedTags : $this->contentTags;

        return array_map('stripcslashes', $tags);
    }

    /**
     * Set the echo format to be used by the compiler.
     *
     * @param  string  $format
     *
     * @return void
     */
    public function setEchoFormat(string $format): void
    {
        $this->echoFormat = $format;
    }
}
