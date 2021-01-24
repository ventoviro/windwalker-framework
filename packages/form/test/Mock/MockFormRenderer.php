<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Test\Mock;

use Windwalker\DOM\DOMElement;
use Windwalker\Form\Field\AbstractField;
use Windwalker\Form\Renderer\FormRendererInterface;

use function Windwalker\DOM\h;

/**
 * The MockFormRenderer class.
 *
 * @since  3.0
 */
class MockFormRenderer implements FormRendererInterface
{
    /**
     * @inheritDoc
     */
    public function renderField(AbstractField $field, DOMElement $wrapper, array $options = []): string
    {
        $wrapper = h(
            'mock',
            $wrapper->getAttributes(true),
        );

        $wrapper->appendChild($field->renderLabel($options));
        $wrapper->appendChild($field->renderInput($options));

        return (string) $wrapper;
    }

    /**
     * @inheritDoc
     */
    public function renderLabel(AbstractField $field, DOMElement $label, array $options = []): string
    {
        return 'Hello ';
    }

    /**
     * @inheritDoc
     */
    public function renderInput(AbstractField $field, DOMElement $input, array $options = []): string
    {
        return 'World: ' . $field->getInputName();
    }
}
