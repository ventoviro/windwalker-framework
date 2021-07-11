<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

/**
 * The SimpleTemplate class.
 *
 * @since  2.1.8
 */
class SimpleTemplate
{
    /**
     * @var  array
     */
    protected array $wrapper = ['{{', '}}'];

    /**
     * @var  string
     */
    protected string $delimiter = '.';

    /**
     * @var string
     */
    protected string $template = '';

    public function __construct(string $template, string $delimiter = '.', ?array $wrapper = null)
    {
        $this->delimiter = $delimiter;
        $this->template = $template;

        if ($wrapper !== null) {
            $this->wrapper = $wrapper;
        }
    }

    public static function create(string $template, string $delimiter = '.', ?array $wrapper = null): SimpleTemplate
    {
        return new static($template, $delimiter, $wrapper);
    }

    public function __invoke(array $data = []): string
    {
        return $this->renderTemplate($data);
    }

    public function renderTemplate(array $data = []): string
    {
        [$begin, $end] = $this->wrapper;

        $regex = preg_quote($begin) . '\s*(.+?)\s*' . preg_quote($end);

        return preg_replace_callback(
            chr(1) . $regex . chr(1),
            function ($match) use ($data) {
                $return = Arr::get($data, $match[1], $this->delimiter);

                if (is_array($return) || is_object($return)) {
                    return TypeCast::toString($return);
                }

                return $return;
            },
            $this->template
        );
    }

    /**
     * Parse variable and replace it. This method is a simple template engine.
     *
     * Example: The {{ foo.bar.yoo }} will be replace to value of `$data['foo']['bar']['yoo']`
     *
     * @param  string      $string  The template to replace.
     * @param  array       $data    The data to find.
     * @param  string      $delimiter
     * @param  array|null  $wrapper
     *
     * @return  string Replaced template.
     */
    public static function render(
        string $string,
        array $data = [],
        string $delimiter = '.',
        ?array $wrapper = null
    ): string {
        return (new static($string, $delimiter, $wrapper))->renderTemplate($data);
    }

    public function setVarWrapper(string $start, string $end): SimpleTemplate
    {
        $this->wrapper = [$start, $end];

        return $this;
    }

    /**
     * Method to get property Delimiter
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * Method to set property delimiter
     *
     * @param  string  $delimiter
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setDelimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param  string  $template
     *
     * @return  static  Return self to support chaining.
     */
    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }
}
