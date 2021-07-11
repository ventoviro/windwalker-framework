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

/**
 * The ButtonField class.
 *
 * @method  $this  content(string|callable $value = null)
 * @method  mixed  getContent()
 *
 * @since  2.1.8
 */
class CustomHtmlField extends AbstractField
{
    /**
     * buildInput
     *
     * @param  DOMElement  $input
     * @param  array       $options
     *
     * @return  mixed
     */
    public function buildFieldElement(DOMElement $input, array $options = []): string
    {
        $content = $this->getContent();

        if (is_callable($content)) {
            return $content($this, $input->getAttributes(true), $options);
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function prepareInput(DOMElement $input): DOMElement
    {
        return $input;
    }
}
