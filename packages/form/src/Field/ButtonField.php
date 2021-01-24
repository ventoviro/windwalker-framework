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
use Windwalker\DOM\HTMLFactory;

use function Windwalker\DOM\h;

/**
 * The ButtonField class.
 *
 * @method  $this  text(string $value = null)
 * @method  mixed  getText()
 * @method  $this  buttonType(string $value)
 * @method  mixed  getButtonType()
 * @method  $this  element(string $value = null)
 * @method  mixed  getElement()
 * @method  $this  href(string $value = null)
 * @method  mixed  getHref()
 * @method  $this  target(string $value = null)
 * @method  mixed  getTarget()
 *
 * @since  2.1.8
 */
class ButtonField extends AbstractField
{
    public const ELEMENT_BUTTON = 'button';

    public const ELEMENT_LINK = 'link';

    public const TYPE_BUTTON = 'button';

    public const TYPE_CLEAR = 'clear';

    public const TYPE_SUBMIT = 'submit';

    /**
     * @inheritDoc
     */
    public function prepareInput(DOMElement $input): DOMElement
    {
        $text = $this->getText() ?? $this->getValue();

        $input = h(
            $this->getElement() ?? static::ELEMENT_BUTTON,
            $input->getAttributes(true),
            $text ? HTMLFactory::parse((string) $text, HTMLFactory::TEXT_SPAN) : ''
        );

        $input['type'] = $this->getButtonType() ?? static::ELEMENT_BUTTON;
        $input['id']   = $this->getId();

        return $input;
    }
}
