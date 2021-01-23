<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Schema\Ddl;

use Windwalker\Database\Platform\Type\DataType;
use Windwalker\Query\Grammar\MySQLGrammar;
use Windwalker\Query\Query;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\TypeCast;

use function Windwalker\raw;

/**
 * The Column class.
 */
class Column
{
    use WrapableTrait;
    use OptionAccessTrait;

    public string $columnName = '';

    protected ?int $ordinalPosition = 1;

    /**
     * @var mixed
     */
    protected mixed $columnDefault = null;

    protected bool $isNullable = false;

    protected ?string $dataType = null;

    protected int|string|null $characterMaximumLength = null;

    protected ?int $characterOctetLength = null;

    protected ?int $numericPrecision = null;

    protected ?int $numericScale = null;

    protected bool $numericUnsigned = false;

    protected ?string $comment = null;

    protected bool $autoIncrement = false;

    protected array $erratas = [];

    public function __construct(
        string $name = '',
        ?string $dataType = null,
        bool $isNullable = false,
        mixed $columnDefault = null,
        array $options = []
    ) {
        $this->columnName    = $name;
        $this->columnDefault = $columnDefault;
        $this->isNullable    = $isNullable;
        $this->dataType((string) $dataType);

        $this->bind($options);
    }

    /**
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * @param  string  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function columnName(string $name): static
    {
        $this->columnName = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrdinalPosition(): ?int
    {
        return $this->ordinalPosition;
    }

    /**
     * @param  int|null  $ordinalPosition
     *
     * @return  static  Return self to support chaining.
     */
    public function ordinalPosition(int|string|null $ordinalPosition): static
    {
        $this->ordinalPosition = TypeCast::tryInteger($ordinalPosition, true);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumnDefault(): mixed
    {
        return $this->columnDefault;
    }

    /**
     * @param  mixed  $value
     *
     * @return  static  Return self to support chaining.
     */
    public function columnDefault(mixed $value): static
    {
        $this->columnDefault = $value;

        return $this;
    }

    public function canHasDefaultValue(): bool
    {
        return $this->getColumnDefault() !== false && !($this->getColumnDefault() === null && !$this->getIsNullable());
    }

    /**
     * Alias of setColumnDefault().
     *
     * @param  mixed  $value
     *
     * @return  static  Return self to support chaining.
     */
    public function defaultValue(mixed $value): static
    {
        return $this->columnDefault($value);
    }

    public function defaultCurrent(): static
    {
        return $this->defaultValue(raw('CURRENT_TIMESTAMP'));
    }

    public function onUpdateCurrent(bool $bool = true): static
    {
        return $this->setOption('on_update', $bool);
    }

    /**
     * @return bool
     */
    public function getIsNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * @param  bool  $isNullable
     *
     * @return  static  Return self to support chaining.
     */
    public function isNullable(bool $isNullable): static
    {
        $this->isNullable = $isNullable;

        return $this;
    }

    public function nullable(bool $isNullable): static
    {
        return $this->isNullable($isNullable);
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * @param  string  $dataType
     *
     * @return  static  Return self to support chaining.
     */
    public function dataType(string $dataType): static
    {
        if (str_contains($dataType, '(')) {
            [$dataType, $precision, $scale] = DataType::extract($dataType);

            $this->dataType = $dataType;

            $this->setLengthByType(
                TypeCast::tryInteger($precision, true),
                TypeCast::tryInteger($scale, true)
            );
        } else {
            $this->dataType = $dataType;
        }

        return $this;
    }

    public function position(string $delta, string $pos): static
    {
        $this->setOption('position', [$delta, $pos]);

        return $this;
    }

    public function before(string $column): static
    {
        return $this->position('BEFORE', $column);
    }

    public function after(string $column): static
    {
        return $this->position('AFTER', $column);
    }

    /**
     * length
     *
     * @param  int|string|null  $value
     *
     * @return  static
     */
    public function length(string|int|null $value): static
    {
        if ($value === null) {
            $this->setLengthByType(null, null);
        }

        [$dataType, $precision, $scale] = DataType::extract("{$this->dataType}($value)");

        $this->setLengthByType(
            $precision,
            TypeCast::tryInteger($scale, true)
        );

        return $this;
    }

    private function setLengthByType(int|string|null $precision, ?int $scale): void
    {
        if ($this->isNumeric()) {
            $this->numericPrecision($precision);
            $this->numericScale($scale);

            return;
        }

        $this->characterMaximumLength($precision);
    }

    public function getLengthExpression(): ?string
    {
        if ((string) $this->characterMaximumLength !== '') {
            return (string) $this->characterMaximumLength;
        }

        if ((string) $this->numericPrecision !== '' || $this->numericScale !== null) {
            return implode(',', array_filter([$this->numericPrecision, $this->numericScale]));
        }

        return null;
    }

    public function isNumeric(): bool
    {
        $type = $this->dataType;

        return in_array(
            strtolower($type),
            [
                'int',
                'integer',
                'tinyint',
                'tinyinteger',
                'bigint',
                'biginteger',
                'smallint',
                'smallinteger',
                'float',
                'double',
                'real',
                'decimal',
                'numeric',
            ],
            true
        );
    }

    /**
     * @return int|string|null
     */
    public function getCharacterMaximumLength(): int|string|null
    {
        return $this->characterMaximumLength;
    }

    /**
     * @param  int|string  $characterMaximumLength
     *
     * @return  static  Return self to support chaining.
     */
    public function characterMaximumLength(int|string|null $characterMaximumLength): static
    {
        $this->characterMaximumLength = $characterMaximumLength;

        return $this;
    }

    /**
     * @return int
     */
    public function getCharacterOctetLength(): ?int
    {
        return $this->characterOctetLength;
    }

    /**
     * @param  int|null  $characterOctetLength
     *
     * @return  static  Return self to support chaining.
     */
    public function characterOctetLength(int|string|null $characterOctetLength): static
    {
        $this->characterOctetLength = TypeCast::tryInteger($characterOctetLength, true);

        return $this;
    }

    /**
     * @return int
     */
    public function getNumericPrecision(): ?int
    {
        return $this->numericPrecision;
    }

    public function numericPrecision(int|string|null $precision): static
    {
        $this->numericPrecision = TypeCast::tryInteger($precision, true);

        return $this;
    }

    /**
     * @return int
     */
    public function getNumericScale(): ?int
    {
        return $this->numericScale;
    }

    /**
     * @param  int  $scale
     *
     * @return  static  Return self to support chaining.
     */
    public function numericScale(int|string|null $scale): static
    {
        $this->numericScale = TypeCast::tryInteger($scale, true);

        return $this;
    }

    /**
     * @return bool
     */
    public function getNumericUnsigned(): bool
    {
        return $this->numericUnsigned;
    }

    /**
     * @param  bool  $unsigned
     *
     * @return  static  Return self to support chaining.
     */
    public function unsigned(bool $unsigned = true): static
    {
        $this->numericUnsigned = $unsigned;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param  string|null  $comment
     *
     * @return  static  Return self to support chaining.
     */
    public function comment(?string $comment = null): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function primary(bool $primary = true): static
    {
        $this->setOption('primary', $primary);

        return $this;
    }

    public function isPrimary(): bool
    {
        return (bool) $this->getOption('primary');
    }

    /**
     * @return bool
     */
    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    /**
     * @param  bool  $autoIncrement
     *
     * @return  static  Return self to support chaining.
     */
    public function autoIncrement(bool $autoIncrement = true): static
    {
        $this->autoIncrement = $autoIncrement;

        return $this;
    }

    /**
     * @return array
     */
    public function getErratas(): array
    {
        return $this->erratas;
    }

    /**
     * @param  array  $erratas
     *
     * @return  static  Return self to support chaining.
     */
    public function erratas(array $erratas): static
    {
        $this->erratas = $erratas;

        return $this;
    }

    public function getTypeExpression(): string
    {
        $expr = $this->dataType;

        $length = $this->getLengthExpression();

        if ($length !== null) {
            $expr .= '(' . $length . ')';
        }

        return $expr;
    }

    public function getCreateExpression(Query $query): string
    {
        $expr = $this->getTypeExpression();

        if (!$this->isNullable) {
            $expr .= ' NOT NULL';
        }

        if ($this->columnDefault !== null || $this->isNullable) {
            $expr .= ' DEFAULT ' . $query->quote($this->columnDefault);
        }

        if ($query->getGrammar() instanceof MySQLGrammar) {
            if ($this->comment !== null) {
                $expr .= ' COMMENT ' . $query->quote($this->columnDefault);
            }

            if ($this->getOption('position') !== null) {
                [$delta, $pos] = $this->getOption('position');
                $expr .= ' POSITION ' . $delta . ' ' . $query->quoteName($pos);
            }
        }

        return $expr;
    }
}
