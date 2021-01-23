<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data\Format;

/**
 * The CallbackFormatHandler class.
 *
 * @since  __DEPLOY_VERSION__
 */
class CallbackFormatHandler implements FormatInterface
{
    /**
     * @var  callable|null
     */
    protected $parser;

    /**
     * @var  callable|null
     */
    protected $dumper;

    /**
     * CallbackFormatHandler constructor.
     *
     * @param  callable|null  $parser
     * @param  callable|null  $dumper
     */
    public function __construct(?callable $parser = null, ?callable $dumper = null)
    {
        $this->setParser($parser);
        $this->setDumper($dumper);
    }

    /**
     * Converts an object into a formatted string.
     *
     * @param  array|object  $data     Data Source Object.
     * @param  array         $options  An array of options for the formatter.
     *
     * @return  string  Formatted string.
     *
     * @since   2.0
     */
    public function dump($data, array $options = []): string
    {
        return ($this->dumper)($data, $options);
    }

    /**
     * Converts a formatted string into an object.
     *
     * @param  string  $string   Formatted string
     * @param  array   $options  An array of options for the formatter.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function parse(string $string, array $options = []): mixed
    {
        return ($this->parser)($string, $options);
    }

    /**
     * Method to get property Parser
     *
     * @return  callable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getParser(): callable
    {
        return $this->parser;
    }

    /**
     * Method to set property parser
     *
     * @param  callable  $parser
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setParser(?callable $parser): static
    {
        $this->parser = $parser
            ?? static function (): string {
                return '';
            };

        return $this;
    }

    /**
     * Method to get property Dumper
     *
     * @return  callable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getDumper(): callable
    {
        return $this->dumper;
    }

    /**
     * Method to set property dumper
     *
     * @param  callable  $dumper
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setDumper(?callable $dumper): static
    {
        $this->dumper = $dumper
            ?? static function (): array {
                return [];
            };

        return $this;
    }
}
