<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Component;

use Closure;
use Windwalker\Utilities\Attributes\Prop;

/**
 * The AnonymousComponent class.
 */
class AnonymousComponent extends AbstractComponent
{
    #[Prop]
    public string $view = '';

    #[Prop]
    public array $data = [];

    /**
     * @inheritDoc
     */
    public function render(): Closure|string
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
        $this->attributes ??= $this->newAttributeBag();

        $attributes = $this->data['attributes'] ?? [];

        if ($attributes instanceof ComponentAttributes) {
            $attributes = $attributes->getAttributes();
        }

        return array_merge(
            $attributes,
            $this->attributes->getAttributes(),
            $this->data,
            ['attributes' => $this->attributes]
        );
    }
}
