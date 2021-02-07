<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Database\DatabaseAdapter;

/**
 * The ORM class.
 */
class ORM
{
    protected DatabaseAdapter $db;

    /**
     * ORM constructor.
     *
     * @param  DatabaseAdapter  $db
     */
    public function __construct(DatabaseAdapter $db)
    {
        $this->db = $db;
    }

    /**
     * @return DatabaseAdapter
     */
    public function getDb(): DatabaseAdapter
    {
        return $this->db;
    }

    /**
     * @param  DatabaseAdapter  $db
     *
     * @return  static  Return self to support chaining.
     */
    public function setDb(DatabaseAdapter $db): static
    {
        $this->db = $db;

        return $this;
    }
}
