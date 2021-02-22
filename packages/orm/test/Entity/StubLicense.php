<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\DateTimeCast;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;

/**
 * The StubLicense class.
 */
#[Table('licenses')]
class StubLicense implements EntityInterface
{
    use EntityTrait;

    #[Column('id')]
    protected ?int $id = null;

    #[Column('no')]
    protected string $no = '';

    #[Column('type')]
    protected string $type = '';

    #[Column('target_no')]
    protected string $targetNo = '';

    #[Column('title')]
    protected string $title = '';

    #[Column('created')]
    #[Cast(DateTimeCast::class)]
    protected ?\DateTimeImmutable $created = null;

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
    public function getNo(): string
    {
        return $this->no;
    }

    /**
     * @param  string  $no
     *
     * @return  static  Return self to support chaining.
     */
    public function setNo(string $no): static
    {
        $this->no = $no;

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
     * @return string
     */
    public function getTargetNo(): string
    {
        return $this->targetNo;
    }

    /**
     * @param  string  $targetNo
     *
     * @return  static  Return self to support chaining.
     */
    public function setTargetNo(string $targetNo): static
    {
        $this->targetNo = $targetNo;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param  string  $title
     *
     * @return  static  Return self to support chaining.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreated(): ?\DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @param  \DateTimeImmutable|null  $created
     *
     * @return  static  Return self to support chaining.
     */
    public function setCreated(?\DateTimeImmutable $created): static
    {
        $this->created = $created;

        return $this;
    }
}
