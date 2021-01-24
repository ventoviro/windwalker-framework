<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Handler;

use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Query;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The PdoHandler class.
 */
class PdoHandler extends AbstractHandler
{
    use OptionAccessTrait;

    protected \PDO $db;

    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool
    {
        return class_exists(\PDO::class);
    }

    /**
     * Class init.
     *
     * @param  \PDO   $db
     * @param  array  $options
     */
    public function __construct(\PDO $db, array $options = [])
    {
        $this->db = $db;

        $this->prepareOptions(
            [
                'table' => 'windwalker_sessions',
                'columns' => [
                    'id' => 'id',
                    'data' => 'data',
                    'time' => 'time',
                ],
            ],
            $options
        );
    }

    /**
     * Read the data for a particular session identifier from the SessionHandler backend.
     *
     * @param  string  $id  The session identifier.
     *
     * @return  string  The session data.
     *
     * @throws \Exception
     * @since   2.0
     */
    protected function doRead(string $id): ?string
    {
        $query = $this->createQuery()
            ->select($this->getOption('columns')['data'])
            ->from($this->getOption('table'))
            ->where($this->getOption('columns')['id'], $id);

        $stmt = $this->db->prepare($sql = $query->forPDO($params));

        $stmt->execute($params);
        $item = $stmt->fetchAll(\PDO::FETCH_NUM);

        if ($item) {
            return $item[0][0];
        }

        return null;
    }

    /**
     * Write session data to the SessionHandler backend.
     *
     * @param  string  $id    The session identifier.
     * @param  string  $data  The session data.
     *
     * @return  boolean  True on success, false otherwise.
     * @since   2.0
     */
    public function write($id, $data): bool
    {
        $columns = $this->getOption('columns');

        $mergeSql = $this->getMergeSql();

        if ($mergeSql !== null) {
            $stmt = $this->db->prepare($mergeSql);
            $stmt->bindValue('id', $id, \PDO::PARAM_STR);
            $stmt->bindValue('data', $data, \PDO::PARAM_STR);
            $stmt->bindValue('time', time(), \PDO::PARAM_INT);

            $stmt->execute();

            return true;
        }

        $this->db->beginTransaction();

        $item = [
            $columns['data'] => $data,
            $columns['time'] => (int) time(),
            $columns['id'] => $id,
        ];

        $query = $this->createQuery()
            ->select('*')
            ->from($this->getOption('table'))
            ->where($columns['id'], $id)
            ->forUpdate();

        try {
            $stmt = $this->db->prepare($query->forPDO($params));
            $stmt->execute($params);
            $sess = $stmt->fetchObject();

            if ($sess === null) {
                $query = $this->createQuery()
                    ->insert($this->getOption('table'))
                    ->columns(array_values($this->getOption('columns')))
                    ->values(array_values($item));

                $stmt = $this->db->prepare($query->forPDO($params));
                $stmt->execute($params);
                $this->db->commit();

                return true;
            }

            $query = $this->createQuery()
                ->update($this->getOption('table'));

            foreach ($item as $k => $v) {
                $query->set($k, $v);
            }

            $stmt = $this->db->prepare($query->forPDO($params));
            $stmt->execute($params);
            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * updateTimestamp
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function updateTimestamp($id, $data): bool
    {
        $columns = $this->getOption('columns');

        $query = $this->createQuery()
            ->update($this->getOption('table'))
            ->set($columns['time'], time())
            ->where($columns['id'], $id);

        $stmt = $this->db->prepare($query->forPDO($params));
        $stmt->execute($params);

        return true;
    }

    /**
     * Destroy the data for a particular session identifier in the SessionHandler backend.
     *
     * @param  string  $id  The session identifier.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @throws \Exception
     * @since   2.0
     */
    public function destroy($id): bool
    {
        $columns = $this->getOption('columns');

        $query = $this->createQuery()
            ->delete($this->getOption('table'))
            ->where($columns['id'], $id);

        $stmt = $this->db->prepare($query->forPDO($params));
        $stmt->execute($params);

        return true;
    }

    /**
     * Garbage collect stale sessions from the SessionHandler backend.
     *
     * @param  int  $lifetime  The maximum age of a session.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @throws  \Exception
     * @since   2.0
     */
    public function gc($lifetime): bool
    {
        // Determine the timestamp threshold with which to purge old sessions.
        $past = time() - $lifetime;

        $query = $this->createQuery()
            ->delete($this->getOption('table'))
            ->where($this->getOption('columns')['time'], '<', $past);

        $stmt = $this->db->prepare($query->forPDO($params));
        $stmt->execute($params);

        return true;
    }

    /**
     * Returns a merge/upsert (i.e. insert or update) SQL query when supported by the database.
     *
     * @return string|null The SQL string or null when not supported
     */
    private function getMergeSql(): ?string
    {
        $platformName = $this->getPlatformName();

        $columns = $this->getOption('columns');
        $table   = $this->getOption('table');

        $query = $this->createQuery();

        switch ($platformName) {
            case AbstractPlatform::MYSQL:
                return $query->format(
                    "INSERT INTO %n (%n, %n, %n) VALUES (:id, :data, :time)
ON DUPLICATE KEY UPDATE %n = VALUES(%n), %n = VALUES(%n)",
                    $table,
                    $columns['id'],
                    $columns['data'],
                    $columns['time'],
                    $columns['data'],
                    $columns['data'],
                    $columns['time'],
                    $columns['time']
                );

            case 'oci':
                // DUAL is Oracle specific dummy table
                return $query->format(
                    " MERGE INTO %n USING DUAL ON (%n = :id) WHEN NOT MATCHED
                    THEN INSERT (%n, %n, %n) VALUES (:id, :data, :time) WHEN MATCHED
                    THEN UPDATE SET %n = :data, %n = :time",
                    $table,
                    $columns['id'],
                    $columns['id'],
                    $columns['data'],
                    $columns['time'],
                    $columns['data'],
                    $columns['time']
                );

            case AbstractPlatform::SQLSERVER === $platformName
                && version_compare(
                    $this->db->getDriver()->getVersion(),
                    '10',
                    '>='
                ):
                // @codingStandardsIgnoreStart
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                return $query->format(
                    "MERGE INTO %n WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON (%n = :id)
                    WHEN NOT MATCHED THEN INSERT (%n, %n, %n) VALUES (:id, :data, :time)
                    WHEN MATCHED THEN UPDATE SET %n = :data, %n = :time;",
                    $table,
                    $columns['id'],
                    $columns['id'],
                    $columns['data'],
                    $columns['time'],
                    $columns['data'],
                    $columns['time']
                );

            case AbstractPlatform::SQLITE:
                return $query->format(
                    "INSERT OR REPLACE INTO %n (%n, %n, %n) VALUES (:id, :data, :time)",
                    $table,
                    $columns['id'],
                    $columns['data'],
                    $columns['time']
                );
        }

        return null;
    }

    protected function createQuery(): Query
    {
        $platform = $this->getPlatformName();

        return new Query($this->db, AbstractGrammar::create($platform));
    }

    /**
     * getPlatformName
     *
     * @return  string
     */
    protected function getPlatformName(): string
    {
        $platform = (string) $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        switch (strtolower($platform)) {
            case 'pgsql':
            case 'postgresql':
                $platform = 'PostgreSQL';
                break;

            case 'sqlsrv':
            case 'sqlserver':
                $platform = 'SQLServer';
                break;

            case 'mysql':
                $platform = 'MySQL';
                break;

            case 'sqlite':
                $platform = 'SQLite';
                break;
        }

        return $platform;
    }
}
