<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\DOMElement;
use Windwalker\Form\FormNormalizer;

use function Windwalker\DOM\h;

/**
 * The RadioField class.
 *
 * @since  2.0
 */
class RadioField extends ListField
{
    public function buildFieldElement(DOMElement $input, array $options = []): string|DOMElement
    {
        $attrs = $input->getAttributes(true);
        unset($attrs['name']);

        $input = h('div', $attrs);

        foreach ($this->getOptions() as $option) {
            $option = clone $option;
            $option['type'] = 'radio';
            $option['name'] = $this->getInputName();
            $option['id'] = $this->getId('-' . FormNormalizer::clearAttribute($option['value']));
            $option['data-radio-item-input'] = true;

            if ((string) $option['value'] === (string) $this->getValue()) {
                $option['checked'] = 'checked';
            }

            FormNormalizer::sortAttributes($option, ['id', 'value', 'name']);

            $input->appendChild(
                h(
                    'div',
                    [
                        'id' => $option['id'] . '-item',
                        'class' => 'radio',
                        'data-radio-item-wrapper' => true,
                    ],
                    [
                        h('input', $option->getAttributes(true)),
                        h(
                            'label',
                            [
                                'for' => $option['id'],
                                'id' => $option['id'] . '-label',
                                'data-radio-item-label' => true,
                            ],
                            $option->childNodes
                        ),
                    ]
                )
            );
        }

        return $input;
    }
}
