<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Renderer;

use Windwalker\DOM\DOMElement;
use Windwalker\Form\Field\AbstractField;

/**
 * The FormRendererInterface class.
 *
 * @since  3.0
 */
interface FormRendererInterface
{
    /**
     * renderField
     *
     * @param  AbstractField  $field
     * @param  array          $wrapper
     * @param  array          $options
     *
     * @return string
     */
    public function renderField(AbstractField $field, DOMElement $wrapper, array $options = []): string;

    /**
     * renderLabel
     *
     * @param  AbstractField  $field
     * @param  DOMElement     $label
     * @param  array          $options
     *
     * @return string
     */
    public function renderLabel(AbstractField $field, DOMElement $label, array $options = []): string;

    /**
     * renderInput
     *
     * @param  AbstractField  $field
     * @param  DOMElement     $input
     * @param  array          $options
     *
     * @return string
     */
    public function renderInput(AbstractField $field, DOMElement $input, array $options = []): string;
}
