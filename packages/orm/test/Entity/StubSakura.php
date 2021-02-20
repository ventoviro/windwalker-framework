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
use Windwalker\ORM\Event\AfterSaveEvent;

/**
 * The Sakura class.
 */
#[Table('sakuras')]
class StubSakura
{
    #[PK, AutoIncrement]
    #[Column('id')]
    protected ?int $id = null;

    #[Column('no')]
    protected string $no = '';

    #[Column('location_no')]
    protected string $locationNo = '';

    #[Column('rose_no')]
    protected string $roseNo = '';

    #[Column('title')]
    protected string $title = '';

    #[Column('state')]
    protected int $state = 0;

    #[AfterSaveEvent]
    public static function afterSave(AfterSaveEvent $event)
    {
        $data = $event->getData();

        if (!empty($data['id']) && empty($data['no'])) {
            $data['no'] = 'S' . str_pad((string) $data['id'], 5, '0', STR_PAD_LEFT);

            $event->getORM()
                ->updateBatch(
                    static::class,
                    ['no' => $data['no']],
                    ['id' => $data['id']]
                );

            $event->setData($data);
        }
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

    /**
     * @return string
     */
    public function getLocationNo(): string
    {
        return $this->locationNo;
    }

    /**
     * @param  string  $locationNo
     *
     * @return  static  Return self to support chaining.
     */
    public function setLocationNo(string $locationNo): static
    {
        $this->locationNo = $locationNo;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoseNo(): string
    {
        return $this->roseNo;
    }

    /**
     * @param  string  $roseNo
     *
     * @return  static  Return self to support chaining.
     */
    public function setRoseNo(string $roseNo): static
    {
        $this->roseNo = $roseNo;

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
}
