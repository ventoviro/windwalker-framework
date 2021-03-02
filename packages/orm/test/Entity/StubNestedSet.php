<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\NestedSet;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;

/**
 * The StubNestedset class.
 */
#[Table('#__nestedsets'), NestedSet]
class StubNestedSet
{
    #[Column('id'), PK, AutoIncrement]
    protected ?int $id = null;

    #[Column('parent_id')]
    protected int $parentId = 0;

    #[Column('lft')]
    protected int $lft = 0;

    #[Column('rgt')]
    protected int $rgt = 0;

    #[Column('level')]
    protected int $level = 0;

    #[Column('title')]
    protected string $title = '';

    #[Column('alias')]
    protected string $alias = '';

    #[Column('access')]
    protected int $access = 0;

    #[Column('path')]
    protected string $path = '';

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
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @param  int  $parentId
     *
     * @return  static  Return self to support chaining.
     */
    public function setParentId(int $parentId): static
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLft(): int
    {
        return $this->lft;
    }

    /**
     * @param  int  $lft
     *
     * @return  static  Return self to support chaining.
     */
    public function setLft(int $lft): static
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * @return int
     */
    public function getRgt(): int
    {
        return $this->rgt;
    }

    /**
     * @param  int  $rgt
     *
     * @return  static  Return self to support chaining.
     */
    public function setRgt(int $rgt): static
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param  int  $level
     *
     * @return  static  Return self to support chaining.
     */
    public function setLevel(int $level): static
    {
        $this->level = $level;

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
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param  string  $alias
     *
     * @return  static  Return self to support chaining.
     */
    public function setAlias(string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccess(): int
    {
        return $this->access;
    }

    /**
     * @param  int  $access
     *
     * @return  static  Return self to support chaining.
     */
    public function setAccess(int $access): static
    {
        $this->access = $access;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }
}
