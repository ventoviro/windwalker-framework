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
 * The SimpleRenderer class.
 */
class SimpleRenderer implements FormRendererInterface
{
    /**
     * @inheritDoc
     */
    public function renderField(AbstractField $field, DOMElement $wrapper, array $options = []): string
    {
        return (string) $field->buildWrapper($wrapper, $options);
    }

    /**
     * @inheritDoc
     */
    public function renderLabel(AbstractField $field, DOMElement $label, array $options = []): string
    {
        return (string) $field->buildLabel($label, $options);
    }

    /**
     * @inheritDoc
     */
    public function renderInput(AbstractField $field, DOMElement $input, array $options = []): string
    {
        return (string) $field->buildInput($input, $options);
    }
}
