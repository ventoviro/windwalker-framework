<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form;

use Windwalker\Filter\ValidatorInterface;
use Windwalker\Form\Field\AbstractField;

/**
 * The ValidateResult class.
 *
 * @since  2.0
 */
class ValidateResult
{
    public const STATUS_SUCCESS = 200;

    public const STATUS_REQUIRED = 400;

    public const STATUS_FAILURE = 500;

    protected int $result = self::STATUS_SUCCESS;

    /**
     * @var ?ValidatorInterface
     */
    protected ?ValidatorInterface $validator = null;

    /**
     * Property field.
     *
     * @var AbstractField
     */
    protected AbstractField $field;

    /**
     * ValidateResult constructor.
     *
     * @param  int                 $result
     * @param  AbstractField       $field
     * @param  ValidatorInterface  $validator
     */
    public function __construct(int $result, AbstractField $field, ?ValidatorInterface $validator = null)
    {
        $this->field     = $field;
        $this->result    = $result;
        $this->validator = $validator;
    }

    /**
     * isSuccess
     *
     * @return  bool
     */
    public function isSuccess(): bool
    {
        return $this->result === static::STATUS_SUCCESS;
    }

    /**
     * isFailure
     *
     * @return  bool
     */
    public function isFailure(): bool
    {
        return !$this->isSuccess();
    }

    /**
     * Method to get property Field
     *
     * @return  AbstractField
     */
    public function getField(): AbstractField
    {
        return $this->field;
    }

    /**
     * Method to get property Result
     *
     * @return  int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ?ValidatorInterface
    {
        return $this->validator;
    }
}
