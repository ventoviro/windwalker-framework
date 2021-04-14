<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Component;

/**
 * The AnonymousComponent class.
 */
class AnonymousComponent extends AbstractComponent
{
    /**
     * AnonymousComponent constructor.
     *
     * @param  string  $view
     * @param  array   $data
     */
    public function __construct(public string $view, public array $data)
    {

    }

    /**
     * @inheritDoc
     */
    public function render(): \Closure|string
    {
        return $this->view;
    }

    /**
     * Get the data that should be supplied to the view.
     *
     * @return array
     */
    public function data(): array
    {
        $this->attributes = $this->attributes ?: $this->newAttributeBag();

        return array_merge(
            $this->data['attributes'] ?? [],
            $this->attributes->getAttributes(),
            $this->data,
            ['attributes' => $this->attributes]
        );
    }
}
