<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Driver\TransactionDriverInterface;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Query\Escaper;

/**
 * The PdoDriver class.
 */
class PdoDriver extends AbstractDriver implements TransactionDriverInterface
{
    /**
     * @var string
     */
    protected static $name = 'pdo';

    /**
     * @var string
     */
    protected $platformName = 'odbc';

    protected function getConnectionClass(): string
    {
        return sprintf(
            __NAMESPACE__ . '\Pdo%sConnection',
            ucfirst(AbstractPlatform::getShortName($this->platformName))
        );
    }

    /**
     * @inheritDoc
     */
    public function doPrepare(string $query, array $bounded = [], array $options = []): StatementInterface
    {
        /** @var \PDO $pdo */
        $pdo = $this->connect()->get();

        $exec = $options['exec'] ?? null;

        unset($options['exec']);

        return new PdoStatement(
            static function () use ($pdo, $query, $options, $bounded, $exec) {
                return [
                    $pdo->prepare($query, $options),
                    static function (PdoStatement $stmt) use ($bounded) {
                        foreach ($bounded as $key => $bound) {
                            $key = is_int($key) ? $key + 1 : $key;

                            $stmt->bindParam(
                                $key,
                                $bound['value'],
                                $bound['dataType'] ?? null,
                                $bound['length'] ?? 0,
                                $bound['driverOptions'] ?? null
                            );
                        }
                    }
                ];
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function execute(mixed $query, ?array $params = null): StatementInterface
    {
        return $this->prepare($query, ['exec' => true])->execute($params);
    }

    /**
     * @inheritDoc
     */
    public function lastInsertId(?string $sequence = null): ?string
    {
        /** @var \PDO $pdo */
        $pdo = $this->connect()->get();

        return $pdo->lastInsertId($sequence);
    }

    /**
     * @inheritDoc
     */
    public function quote(string $value): string
    {
        /** @var \PDO $pdo */
        $pdo = $this->connect()->get();

        return $pdo->quote($value);
    }

    /**
     * @inheritDoc
     */
    public function escape(string $value): string
    {
        return Escaper::stripQuote($this->quote($value));
    }

    /**
     * @inheritDoc
     */
    public function transactionStart(): bool
    {
        /** @var \PDO $pdo */
        $pdo = $this->connect()->get();

        return $pdo->beginTransaction();
    }

    /**
     * @inheritDoc
     */
    public function transactionCommit(): bool
    {
        /** @var \PDO $pdo */
        $pdo = $this->connect()->get();

        return $pdo->commit();
    }

    /**
     * @inheritDoc
     */
    public function transactionRollback(): bool
    {
        /** @var \PDO $pdo */
        $pdo = $this->connect()->get();

        return $pdo->rollBack();
    }

    /**
     * getVersion
     *
     * @return  string
     */
    public function getVersion(): string
    {
        /** @var \PDO $pdo */
        $pdo = $this->connect()->get();

        return $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }
}
