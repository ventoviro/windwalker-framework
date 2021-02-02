<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Schema;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Query\Grammar\AbstractGrammar;

/**
 * The AbstractSchemaManager class.
 */
abstract class AbstractSchemaManager
{
    protected $platform = '';

    /**
     * @var DatabaseAdapter
     */
    protected $db;

    /**
     * AbstractSchema constructor.
     *
     * @param  DatabaseAdapter  $db
     */
    public function __construct(DatabaseAdapter $db)
    {
        $this->db = $db;
    }

    public static function create(string $platform, DatabaseAdapter $db)
    {
        $class = __NAMESPACE__ . '\\' . DatabaseFactory::getPlatformName($platform) . 'SchemaManager';

        return new $class($db);
    }

    /**
     * @return string
     */
    public function getPlatformName(): string
    {
        return $this->platform;
    }

    public function getPlatform(): AbstractPlatform
    {
        return $this->db->getPlatform();
    }

    /**
     * getGrammar
     *
     * @return  AbstractGrammar
     */
    public function getGrammar(): AbstractGrammar
    {
        return $this->getPlatform()->getGrammar();
    }
}
