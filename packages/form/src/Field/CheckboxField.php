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
 * The CheckboxField class.
 *
 * @since  2.0
 */
class CheckboxField extends AbstractInputField
{
    protected string $inputType = 'checkbox';

    /**
     * @inheritDoc
     */
    public function prepareInput(DOMElement $input): DOMElement
    {
        $input['type']    = $this->getInputType();
        $input['checked'] = $this->getValue() ? 'checked' : null;

        return $input;
    }
}
