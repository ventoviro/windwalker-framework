<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Platform\Type;

/**
 * The SQLServerDataType class.
 *
 * @see https://docs.microsoft.com/zh-tw/sql/ssma/mysql/project-settings-type-mapping-mysqltosql?view=sql-server-2017
 * @see https://support.dbconvert.com/hc/en-us/articles/202952551-Mapping-MySQL-and-SQL-Server-Data-Types
 */
class SQLServerDataType extends DataType
{
    // BOOLEAN
    public const BOOLEAN = 'bit';

    // CHARACTER
    public const NCHAR = 'nchar';

    public const NVARCHAR = 'nvarchar';

    public const NTEXT = 'ntext';

    // EXACT NUMERIC
    public const INTEGER = 'int';

    public const DATETIME2 = 'datetime2';

    /**
     * "Length", "Default", "PHP Type"
     *
     * @var  array
     */
    public static array $typeDefinitions = [
        self::INTEGER => [null, 0, 'int'],
        self::BIGINT => [null, 0, 'int'],
        self::SMALLINT => [null, 0, 'int'],
        self::TINYINT => [null, 0, 'int'],
        self::FLOAT => [24, 0, 'float'],
        self::DOUBLE => [53, 0, 'float'],
        self::REAL => [53, 0, 'float'],
        self::NCHAR => [255, 0, 'string'],
        self::NVARCHAR => [255, 0, 'string'],
        self::DATETIME2 => [null, '1900-01-01 00:00:00', 'string'],
    ];

    /**
     * Property typeMapping.
     *
     * @var  array
     */
    protected static array $typeMapping = [
        DataType::INTEGER => 'int',
        DataType::TINYINT => self::SMALLINT,
        DataType::BOOLEAN => self::BIT,
        DataType::VARCHAR => self::NVARCHAR,
        DataType::CHAR => self::NCHAR,
        DataType::TEXT => self::NVARCHAR,
        DataType::LONGTEXT => self::NVARCHAR,
        DataType::DATETIME => self::DATETIME2,
        DataType::TIMESTAMP => self::DATETIME2,
        'json' => self::NVARCHAR,
    ];

    /**
     * Property noLength.
     *
     * @var  array
     */
    protected static array $noLength = [
        self::INTEGER,
        self::SMALLINT,
        self::NTEXT,
    ];
}
