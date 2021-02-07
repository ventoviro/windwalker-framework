<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

/**
 * The DataMapper class.
 */
class DataMapper
{
    protected string $entityClass;

    /**
     * @var ORM
     */
    protected ORM $orm;

    /**
     * DataMapper constructor.
     *
     * @param  string  $entityClass
     * @param  ORM     $orm
     */
    public function __construct(string $entityClass, ORM $orm)
    {
        $this->entityClass = $entityClass;
        $this->orm = $orm;
    }
}
