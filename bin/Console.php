<?php
// phpcs:disable

/**
 * Part of Simple Console project.
 *
 * @copyright  Copyright (C) 2017 Simon Asika.
 * @license    MIT
 */

namespace Asika\SimpleConsole;

/**
 * The Console class.
 *
 * @since  1.0
 */
class Console
{
    /**
     * Property executable.
     *
     * @var  string
     */
    protected $executable;

    /**
     * Property args.
     *
     * @var  array
     */
    protected $args = [];

    /**
     * Property options.
     *
     * @var  array
     */
    protected $options = [];

    /**
     * Property help.
     *
     * @var  string
     */
    protected $help = '';

    /**
     * Property helpIptions.
     *
     * @var  array
     */
    protected $helpOptions = ['h', 'help'];

    /**
     * Property booleanMapping.
     *
     * @var  array
     */
    protected $booleanMapping = [
        0 => ['n', 'no', 'false', 0, '0', true],
        1 => ['y', 'yes', 'true', 1, '1', false, null]
    ];

    /**
     * CliInput constructor.
     *
     * @param  array  $argv
     */
    public function __construct($argv = null)
    {
        $this->parseArgv($argv ?: $_SERVER['argv']);

        $this->init();
    }

    /**
     * init
     *
     * @return  void
     */
    protected function init()
    {
        // Override if necessary
    }

    /**
     * execute
     *
     * @param  \Closure|null  $callback
     *
     * @return  int
     */
    public function execute(\Closure $callback = null): int
    {
        try {
            if ($this->getOption($this->helpOptions)) {
                $this->out($this->getHelp());

                return 0;
            }

            if ($callback) {
                if (PHP_VERSION_ID >= 50400) {
                    $callback = $callback->bindTo($this);
                }

                $result = call_user_func($callback, $this);
            } else {
                $result = $this->doExecute();
            }
        } catch (\Exception $e) {
            $result = $this->handleException($e);
        } catch (\Throwable $e) {
            $result = $this->handleException($e);
        }

        if ($result === true) {
            $result = 0;
        } elseif ($result === false) {
            $result = 255;
        } else {
            $result = (bool) $result;
        }

        return (int) $result;
    }

    /**
     * doExecute
     *
     * @return  mixed
     */
    protected function doExecute(): mixed
    {
        // Please override this method.
        return 0;
    }

    /**
     * delegate
     *
     * @param  string  $method
     *
     * @return  mixed
     */
    protected function delegate($method): mixed
    {
        $args = func_get_args();
        array_shift($args);

        if (!is_callable([$this, $method])) {
            throw new \LogicException(sprintf('Method: %s not found', $method));
        }

        return call_user_func_array([$this, $method], $args);
    }

    /**
     * getHelp
     *
     * @return  string
     */
    protected function getHelp(): string
    {
        return trim($this->help);
    }

    /**
     * handleException
     *
     * @param  \Exception|\Throwable  $e
     *
     * @return  int
     */
    protected function handleException($e): int
    {
        $v = $this->getOption('v');

        if ($e instanceof CommandArgsException) {
            $this->err('[Warning] ' . $e->getMessage())
                ->err()
                ->err($this->getHelp());
        } else {
            $this->err('[Error] ' . $e->getMessage());
        }

        if ($v) {
            $this->err('[Backtrace]:')
                ->err($e->getTraceAsString());
        }

        $code = $e->getCode();

        return $code === 0 ? 255 : $code;
    }

    /**
     * getArgument
     *
     * @param  int    $offset
     * @param  mixed  $default
     *
     * @return  mixed|null
     */
    public function getArgument($offset, $default = null): mixed
    {
        if (!isset($this->args[$offset])) {
            return $default;
        }

        return $this->args[$offset];
    }

    /**
     * setArgument
     *
     * @param  int    $offset
     * @param  mixed  $value
     *
     * @return  static
     */
    public function setArgument($offset, $value): \static
    {
        $this->args[$offset] = $value;

        return $this;
    }

    /**
     * getOption
     *
     * @param  string|array  $name
     * @param  mixed         $default
     *
     * @return  mixed|null
     */
    public function getOption($name, $default = null): mixed
    {
        $name = (array) $name;

        foreach ($name as $n) {
            if (isset($this->options[$n])) {
                return $this->options[$n];
            }
        }

        return $default;
    }

    /**
     * setOption
     *
     * @param  string|array  $name
     * @param  mixed         $value
     *
     * @return  static
     */
    public function setOption($name, $value): \static
    {
        $name = (array) $name;

        foreach ($name as $n) {
            $this->options[$n] = $value;
        }

        return $this;
    }

    /**
     * out
     *
     * @param  string   $text
     * @param  boolean  $nl
     *
     * @return  static
     */
    public function out($text = null, $nl = true): \static
    {
        fwrite(STDOUT, $text . ($nl ? "\n" : ''));

        return $this;
    }

    /**
     * err
     *
     * @param  string   $text
     * @param  boolean  $nl
     *
     * @return  static
     */
    public function err($text = null, $nl = true): \static
    {
        fwrite(STDERR, $text . ($nl ? "\n" : ''));

        return $this;
    }

    /**
     * in
     *
     * @param  string  $ask
     * @param  mixed   $default
     *
     * @return  string
     */
    public function in($ask = '', $default = null, $bool = false): bool|string
    {
        $this->out($ask, false);

        $in = rtrim(fread(STDIN, 8192), "\n\r");

        if ($bool) {
            $in = $in === '' ? $default : $in;

            return (bool) $this->mapBoolean($in);
        }

        return $in === '' ? (string) $default : $in;
    }

    /**
     * mapBoolean
     *
     * @param  string  $in
     *
     * @return  bool
     */
    public function mapBoolean($in): ?bool
    {
        $in = strtolower((string) $in);

        if (in_array($in, $this->booleanMapping[0], true)) {
            return false;
        }

        if (in_array($in, $this->booleanMapping[1], true)) {
            return true;
        }

        return null;
    }

    /**
     * exec
     *
     * @param  string  $command
     *
     * @return  static
     */
    protected function exec($command): \static
    {
        $this->out('>> ' . $command);

        system($command);

        return $this;
    }

    /**
     * parseArgv
     *
     * @param  array  $argv
     *
     * @return  void
     */
    protected function parseArgv($argv)
    {
        $this->executable = array_shift($argv);
        $key              = null;

        $out = [];

        for ($i = 0, $j = count($argv); $i < $j; $i++) {
            $arg = $argv[$i];

            // --foo --bar=baz
            if (0 === strpos($arg, '--')) {
                $eqPos = strpos($arg, '=');

                // --foo
                if ($eqPos === false) {
                    $key = substr($arg, 2);

                    // --foo value
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $value = $argv[$i + 1];
                        $i++;
                    } else {
                        $value = isset($out[$key]) ? $out[$key] : true;
                    }

                    $out[$key] = $value;
                } else {
                    // --bar=baz
                    $key       = substr($arg, 2, $eqPos - 2);
                    $value     = substr($arg, $eqPos + 1);
                    $out[$key] = $value;
                }
            } elseif (0 === strpos($arg, '-')) {
                // -k=value -abc

                // -k=value
                if (isset($arg[2]) && $arg[2] === '=') {
                    $key       = $arg[1];
                    $value     = substr($arg, 3);
                    $out[$key] = $value;
                } else {
                    // -abc
                    $chars = str_split(substr($arg, 1));

                    foreach ($chars as $char) {
                        $key       = $char;
                        $out[$key] = isset($out[$key]) ? $out[$key] + 1 : 1;
                    }

                    // -a a-value
                    if (($i + 1 < $j) && ($argv[$i + 1][0] !== '-') && (count($chars) === 1)) {
                        $out[$key] = $argv[$i + 1];
                        $i++;
                    }
                }
            } else {
                // Plain-arg
                $this->args[] = $arg;
            }
        }

        $this->options = $out;
    }
}

class CommandArgsException extends \RuntimeException
{
}
