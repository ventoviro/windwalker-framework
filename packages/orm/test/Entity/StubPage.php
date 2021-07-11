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
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Relation\RelationCollection;

/**
 * The StubPage class.
 */
#[Table('pages')]
class StubPage implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    protected ?int $id = null;

    #[Column('no')]
    protected string $no = '';

    #[Column('title')]
    protected string $title = '';

    #[Column('content')]
    protected string $content = '';

    protected ?RelationCollection $pageAttachments = null;

    protected ?RelationCollection $articleAttachments = null;

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
     * @return RelationCollection|null
     */
    public function getPageAttachments(): ?RelationCollection
    {
        return $this->loadCollection('pageAttachments');
    }

    /**
     * @param  RelationCollection|null  $pageAttachments
     *
     * @return  static  Return self to support chaining.
     */
    public function setPageAttachments(?RelationCollection $pageAttachments): static
    {
        $this->pageAttachments = $pageAttachments;

        return $this;
    }

    /**
     * @return RelationCollection|null
     */
    public function getArticleAttachments(): ?RelationCollection
    {
        return $this->loadCollection('articleAttachments');
    }

    /**
     * @param  RelationCollection|null  $articleAttachments
     *
     * @return  static  Return self to support chaining.
     */
    public function setArticleAttachments(?RelationCollection $articleAttachments): static
    {
        $this->articleAttachments = $articleAttachments;

        return $this;
    }
}
