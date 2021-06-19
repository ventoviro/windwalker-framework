<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form;

use Windwalker\Form\Renderer\FormRendererInterface;

/**
 * The FormFactopry class.
 */
class FormRegistry
{
    public static ?Form $form = null;

    public static function form(?Form $form = null): ?Form
    {
        if ($form !== null) {
            static::$form = $form;
        }

        if (static::$form === null) {
            static::$form = static::createForm();
        }

        return static::$form;
    }

    public static function createForm(
        string $namespace = '',
        array $options = [],
        ?FormRendererInterface $renderer = null
    ): Form {
        return new Form($namespace, $options, $renderer);
    }
}
