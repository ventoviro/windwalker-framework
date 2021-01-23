<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Manager;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Platform\AbstractPlatform;

/**
 * The AbstractDbManager class.
 */
abstract class AbstractMetaManager
{
    /**
     * @var string
     */
    protected ?string $name = null;

    /**
     * @var DatabaseAdapter
     */
    protected DatabaseAdapter $db;

    /**
     * AbstractDbManager constructor.
     *
     * @param  string|null      $name
     * @param  DatabaseAdapter  $db
     */
    public function __construct(?string $name, DatabaseAdapter $db)
    {
        $this->db = $db;
        $this->setName($name);
    }

    /**
     * @return DatabaseAdapter
     */
    public function getDb(): DatabaseAdapter
    {
        return $this->db;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getPlatform(): AbstractPlatform
    {
        return $this->db->getPlatform();
    }

    /**
     * reset
     *
     * @return  static
     */
    abstract public function reset(): static;

    /**
     * @param  string  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
