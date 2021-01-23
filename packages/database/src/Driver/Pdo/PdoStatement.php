<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\AbstractStatement;
use Windwalker\Query\Bounded\ParamType;

use function Windwalker\collect;

/**
 * The PdoStatement class.
 *
 * @method \PDOStatement getCursor()
 */
class PdoStatement extends AbstractStatement
{
    /**
     * @var \PDOStatement
     */
    protected $cursor;

    /**
     * @var \Closure|null
     */
    protected ?\Closure $prepare = null;

    /**
     * PdoStatement constructor.
     *
     * @param  \Closure  $prepare
     */
    public function __construct(\Closure $prepare)
    {
        $this->prepare = $prepare;
    }

    private function prepareCursor(): \PDOStatement
    {
        if (!$this->cursor) {
            $prepare = $this->prepare;
            [$this->cursor, $bound] = $prepare();

            $bound($this);
            $this->prepare = null;
        }

        return $this->cursor;
    }

    /**
     * @inheritDoc
     */
    protected function doExecute(?array $params = null): bool
    {
        return (bool) $this->prepareCursor()->execute($params);
    }

    /**
     * @inheritDoc
     */
    public function fetch(array $args = []): ?Collection
    {
        $this->execute();

        $item = $this->prepareCursor()->fetch(\PDO::FETCH_ASSOC);

        return $item !== false ? collect($item) : null;
    }

    /**
     * @inheritDoc
     */
    public function bindParam($key = null, &$value = null, $dataType = null, int $length = 0, $driverOptions = null): static
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->bindParam($k, $v);
            }

            return $this;
        }

        $dataType = $dataType ?? ParamType::guessType($value);

        $this->prepareCursor()->bindParam(
            $key,
            $value,
            ParamType::convertToPDO($dataType),
            $length,
            $driverOptions
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function close(): static
    {
        if ($this->cursor) {
            $this->cursor->closeCursor();
        }

        $this->executed = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function countAffected(): int
    {
        return $this->prepareCursor()->rowCount();
    }
}
