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
use Windwalker\Data\Collection;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\Event\AfterSaveEvent;

/**
 * The Article class.
 */
#[Table('articles')]
class StubArticle
{
    #[Column('id'), PK, AutoIncrement]
    protected ?int $id = null;

    #[Column('category_id')]
    protected int $categoryId;

    #[Column('title')]
    protected string $title = '';

    #[Column('image')]
    protected string $image = '';

    #[Column('content')]
    protected string $content = '';

    #[Column('state')]
    protected int $state = 1;

    #[Column('created')]
    #[Cast(DateTimeImmutable::class)]
    protected DateTimeImmutable $created;

    #[Column('created_by')]
    protected int $createdBy = 0;

    #[Column('params')]
    #[Cast(JsonCast::class)]
    #[Cast('array')]
    protected ?array $params = [];

    #[Cast(StubCategory::class, options: Cast::USE_HYDRATOR)]
    public StubCategory|Collection|null $c = null;

    public static int $counter = 0;

    #[AfterSaveEvent]
    public static function afterSave(
        AfterSaveEvent $event
    ): void {
        static::$counter++;

        $data = $event->getData();
        $data['category_id'] = 2;

        $event->setData($data);
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
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param  string  $image
     *
     * @return  static  Return self to support chaining.
     */
    public function setImage(string $image): static
    {
        $this->image = $image;

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
     * @return DateTimeImmutable
     */
    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @param  DateTimeImmutable  $created
     *
     * @return  static  Return self to support chaining.
     */
    public function setCreated(DateTimeImmutable $created): static
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

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param  array|null  $params
     *
     * @return  static  Return self to support chaining.
     */
    public function setParams(?array $params): static
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param  int  $categoryId
     *
     * @return  static  Return self to support chaining.
     */
    public function setCategoryId(int $categoryId): static
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param  int  $state
     *
     * @return  static  Return self to support chaining.
     */
    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }

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
}
