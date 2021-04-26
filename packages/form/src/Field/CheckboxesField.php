<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\Data\Collection;
use Windwalker\DOM\DOMElement;
use Windwalker\Form\FormNormalizer;

use function Windwalker\DOM\h;

/**
 * The CheckboxesField class.
 *
 * @since  2.0
 */
class CheckboxesField extends ListField
{
    public function buildFieldElement(DOMElement $input, array $options = []): string|DOMElement
    {
        $attrs = $input->getAttributes(true);
        unset($attrs['name']);

        $input = h('div', $attrs);

        foreach ($this->getOptions() as $option) {
            $option                             = clone $option;
            $option['type']                     = 'checkbox';
            $option['name']                     = $this->getInputName('[]');
            $option['id']                       = $this->getId('-' . FormNormalizer::clearAttribute($option['value']));
            $option['data-checkbox-item-input'] = true;

            if (in_array($option['value'], $this->getValue())) {
                $option['checked'] = 'checked';
            }

            FormNormalizer::sortAttributes($option, ['id', 'value', 'name']);

            $input->appendChild(
                h(
                    'div',
                    [
                        'id' => $option['id'] . '-item',
                        'class' => 'checkbox',
                        'data-checkbox-item-wrapper' => true,
                    ],
                    [
                        h('input', $option->getAttributes(true)),
                        h(
                            'label',
                            [
                                'for' => $option['id'],
                                'id' => $option['id'] . '-label',
                                'data-checkbox-item-label' => true,
                            ],
                            $option->childNodes
                        ),
                    ]
                )
            );
        }

        return $input;
    }

    /**
     * getValue
     *
     * @return  array
     */
    public function getValue(): array
    {
        $value = parent::getValue();

        if (!is_array($value)) {
            $value = Collection::explode(',', (string) $value)
                ->map('trim')
                ->filter('strlen')
                ->dump();
        }

        return $value;
    }
}
