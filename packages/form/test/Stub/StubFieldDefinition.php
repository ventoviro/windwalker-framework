<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Test\Stub;

use Windwalker\Filter\InputFilter;
use Windwalker\Filter\Rule\CastTo;
use Windwalker\Filter\Rule\EmailAddress;
use Windwalker\Form\Field\PasswordField;
use Windwalker\Form\Field\TextField;
use Windwalker\Form\FieldDefinitionInterface;
use Windwalker\Form\Form;
use Windwalker\Validator\Rule\EmailValidator;

/**
 * The StubFieldDefinition class.
 *
 * @since  2.0
 */
class StubFieldDefinition implements FieldDefinitionInterface
{
    /**
     * Define the form fields.
     *
     * @param  Form  $form  The Windwalker form object.
     *
     * @return  void
     */
    public function define(Form $form): void
    {
        $form->addField(new TextField('id', 'ID'), 'a')
            ->addWrapperClass('control-input')
            ->addFilter(new CastTo('int'));

        $form->addField(new TextField('u/username', 'Username'), 'a')
            ->required(true)
            ->addClass('control-input');

        $form->addField(new TextField('email', 'Email'), null, 'b')
            ->required(true)
            ->addValidator(EmailAddress::class)
            ->addClass('control-input');

        $form->addField(new PasswordField('password', 'Password'), 'pf', 'b')
            ->addFilter('alnum');

        $form->addField(new TextField('address', 'Address'));
    }
}
