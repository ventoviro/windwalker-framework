<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use PDO;
use Windwalker\Database\Driver\AbstractConnection;

/**
 * The PdoConnection class.
 */
abstract class AbstractPdoConnection extends AbstractConnection
{
    protected static string $name = 'pdo';

    /**
     * @var string
     */
    protected static string $dbtype = '';

    protected static array $defaultAttributes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool
    {
        if (!class_exists(PDO::class)) {
            return false;
        }

        return in_array(strtolower(static::$dbtype), PDO::getAvailableDrivers(), true);
    }

    /**
     * getDsn
     *
     * @param  array  $options
     *
     * @return  string
     */
    public static function getDsn(array $options): string
    {
        return DsnHelper::build($options, static::$dbtype);
    }

    /**
     * @return array
     */
    public static function getDefaultAttributes(): array
    {
        return static::$defaultAttributes;
    }

    /**
     * @param  array  $options
     *
     * @return  PDO
     */
    protected function doConnect(array $options): PDO
    {
        $attrs = array_replace(
            static::getDefaultAttributes(),
            $options['driverOptions'] ?? []
        );

        return new PDO(
            $options['dsn'],
            $options['user'] ?? null,
            $options['password'] ?? null,
            $attrs
        );
    }

    /**
     * @return PDO|null
     */
    public function get(): ?PDO
    {
        return parent::get();
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        $this->connection = null;

        return true;
    }

    /**
     * @return string
     */
    public static function getDbType(): string
    {
        return self::$dbtype;
    }
}
