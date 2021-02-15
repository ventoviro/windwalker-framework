<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Strategy;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Event\HydrateEvent;
use Windwalker\Database\Event\ItemFetchedEvent;
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Arr;

/**
 * The SelectAction class.
 *
 * Query methods.
 */
class Selector extends AbstractQueryStrategy
{
    protected ?string $groupDivider = null;

    public function autoSelections(string $divider = '.'): static
    {
        /** @var array<int, AsClause> $tables */
        $tables = array_values(Arr::collapse($this->getAllTables()));

        $db = $this->getDb();

        foreach ($tables as $i => $clause) {
            $tbm = $db->getTable(
                Query::convertClassToTable($clause->getValue())
            );

            $cols = $tbm->getColumnNames();

            foreach ($cols as $col) {
                $alias = $clause->getAlias();

                if ($i === 0) {
                    $as = $col;
                } else {
                    $as = $alias . $divider . $col;
                }

                $this->selectRaw(
                    '%n AS %r',
                    $alias . '.' . $col,
                    $this->quoteName($as, true)
                );
            }
        }

        return $this;
    }

    public function groupByDivider(string $divider = '.'): static
    {
        $this->groupDivider = $divider;

        return $this;
    }

    public function groupByJoins(string $divider = '.'): static
    {
        return $this->autoSelections($divider)
            ->groupByDivider($divider);
    }

    protected function groupItem(?array $item): ?array
    {
        if ($item === null) {
            return null;
        }

        foreach ($item as $k => $value) {
            if (str_contains($k, $this->groupDivider)) {
                [$prefix, $key] = explode($this->groupDivider, $k, 2);

                $item[$prefix] ??= new Collection();

                $item[$prefix][$key] = $value;

                unset($item[$k]);
            }
        }

        return $item;
    }

    protected function registerEvents(StatementInterface $stmt): StatementInterface
    {
        if ($this->groupDivider !== null) {
            $stmt->on(
                ItemFetchedEvent::class,
                function (ItemFetchedEvent $event) {
                    $item = $this->groupItem($event->getItem());
                    $event->setItem($item);
                }
            );
        }

        $stmt->on(
            HydrateEvent::class,
            function (HydrateEvent $event) {
                $orm  = $this->getORM();
                $item = $event->getItem();

                if ($item === null) {
                    return;
                }

                $object = $event->getClass();

                if (is_string($object)) {
                    $object = $orm->getAttributesResolver()->createObject($object);
                }

                $object = $orm->hydrateEntity($item, $object);

                // Prepare relations
                $orm->getEntityMetadata($object)
                    ->getRelationManager()
                    ->load($item, $object);

                $event->setItem($object);
            }
        );

        return $stmt;
    }

    protected function prepareStatement(): StatementInterface
    {
        return $this->registerEvents(parent::prepareStatement());
    }
}
