<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use DateTimeImmutable;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\DateTimeCast;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;

/**
 * The StubMemberActionMap class.
 */
#[Table('member_action_maps')]
class StubMemberActionMap implements EntityInterface
{
    use EntityTrait;

    #[Column('member_no')]
    protected string $memberNo = '';

    #[Column('action_no')]
    protected string $actionNo = '';

    #[Column('type')]
    protected string $type = '';

    #[Column('created')]
    #[Cast(DateTimeCast::class)]
    protected ?DateTimeImmutable $created = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param  int|null  $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getMemberNo(): string
    {
        return $this->memberNo;
    }

    /**
     * @param  string  $memberNo
     *
     * @return  static  Return self to support chaining.
     */
    public function setMemberNo(string $memberNo): static
    {
        $this->memberNo = $memberNo;

        return $this;
    }

    /**
     * @return string
     */
    public function getActionNo(): string
    {
        return $this->actionNo;
    }

    /**
     * @param  string  $actionNo
     *
     * @return  static  Return self to support chaining.
     */
    public function setActionNo(string $actionNo): static
    {
        $this->actionNo = $actionNo;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  string  $type
     *
     * @return  static  Return self to support chaining.
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @param  DateTimeImmutable|null  $created
     *
     * @return  static  Return self to support chaining.
     */
    public function setCreated(?DateTimeImmutable $created): static
    {
        $this->created = $created;

        return $this;
    }
}
