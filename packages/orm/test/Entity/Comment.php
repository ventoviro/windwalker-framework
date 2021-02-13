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
use Windwalker\ORM\Attributes\CurrentTime;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;

/**
 * The Comment class.
 */
#[Table('comments')]
class Comment
{
    #[Column('id')]
    #[PK(true)]
    protected ?int $id = null;

    #[Column('target_id')]
    #[PK]
    protected int $targetId = 0;

    #[Column('user_id')]
    #[PK]
    protected int $userId = 0;

    #[Column('type')]
    #[PK]
    protected string $type = '';

    #[Column('content')]
    protected string $content = '';

    #[Column('created')]
    #[Cast(\DateTimeImmutable::class)]
    #[CurrentTime]
    protected ?\DateTimeImmutable $created = null;

    #[Column('created_by')]
    protected int $createdBy = 0;

    /**
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param  int  $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getTargetId(): int
    {
        return $this->targetId;
    }

    /**
     * @param  int  $targetId
     *
     * @return  static  Return self to support chaining.
     */
    public function setTargetId(int $targetId): static
    {
        $this->targetId = $targetId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param  int  $userId
     *
     * @return  static  Return self to support chaining.
     */
    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

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
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param  string  $content
     *
     * @return  static  Return self to support chaining.
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreated(): ?\DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @param  \DateTimeImmutable  $created
     *
     * @return  static  Return self to support chaining.
     */
    public function setCreated(\DateTimeImmutable $created): static
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    /**
     * @param  int  $createdBy
     *
     * @return  static  Return self to support chaining.
     */
    public function setCreatedBy(int $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
