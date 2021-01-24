<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Concern;

use InvalidArgumentException;

/**
 * Trait ManageStackTrait
 *
 * todo: Add prepend() supports
 */
trait ManageStackTrait
{
    /**
     * The stack of in-progress push sections.
     *
     * @var array
     */
    protected array $pushStack = [];

    /**
     * Property pushes.
     *
     * @var array
     */
    protected array $pushes = [];

    /**
     * Start injecting content into a push section.
     *
     * @param  string  $section
     * @param  string  $content
     *
     * @return void
     */
    public function startPush(string $section, ?string $content = null): void
    {
        if ($content === null) {
            if (ob_start()) {
                $this->pushStack[] = $section;
            }
        } else {
            $this->extendPush($section, $content);
        }
    }

    /**
     * Stop injecting content into a push section.
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function stopPush(): string
    {
        if (empty($this->pushStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->pushStack);

        $this->extendPush($last, ob_get_clean());

        return $last;
    }

    /**
     * Append content to a given push section.
     *
     * @param  string  $section
     * @param  string  $content
     *
     * @return void
     */
    protected function extendPush(string $section, string $content): void
    {
        if (!isset($this->pushes[$section])) {
            $this->pushes[$section] = [];
        }

        if (!isset($this->pushes[$section][$this->renderCount])) {
            $this->pushes[$section][$this->renderCount] = $content;
        } else {
            $this->pushes[$section][$this->renderCount] .= $content;
        }
    }

    /**
     * Get the string contents of a push section.
     *
     * @param  string  $section
     * @param  string  $default
     *
     * @return string
     */
    public function yieldPushContent(string $section, string $default = ''): string
    {
        if (!isset($this->pushes[$section])) {
            return $default;
        }

        return implode(array_reverse($this->pushes[$section]));
    }
}
