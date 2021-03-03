<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Data\Collection;
use Windwalker\ORM\Exception\NestedHandleException;
use Windwalker\ORM\Nested\NestedEntityInterface;
use Windwalker\Utilities\Assert\ArgumentsAssert;

/**
 * The NestedSetMapper class.
 */
class NestedSetMapper extends EntityMapper
{
    protected const PARENT = 'parent';

    protected const LEFT = 'left';

    protected const RIGHT = 'right';

    /**
     * getAncestors
     *
     * @param  mixed  $pkOrEntity
     *
     * @return  Collection
     */
    public function getAncestors(mixed $pkOrEntity): Collection
    {
        ArgumentsAssert::assert(
            is_object($pkOrEntity) || is_scalar($pkOrEntity),
            '{caller} conditions should be object or scalar, {value} given',
            $pkOrEntity
        );

        $metadata = $this->getMetadata();
        $key      = $metadata->getMainKey();

        $pk = $this->entityToPk($pkOrEntity);

        return $this->getORM()
            ->select('p.*')
            ->from(
                [
                    [$metadata->getClassName(), 'n'],
                    [$metadata->getClassName(), 'p'],
                ]
            )
            ->where('n.lft', 'between', ['p.lft', 'p.rgt'])
            ->where('n.' . $key, '=', $pk)
            ->order('p.lft')
            ->all($metadata->getClassName());
    }

    public function getTree(mixed $pkOrEntity): Collection
    {
        ArgumentsAssert::assert(
            is_object($pkOrEntity) || is_scalar($pkOrEntity),
            '{caller} conditions should be object or scalar, {value} given',
            $pkOrEntity
        );

        $metadata = $this->getMetadata();
        $key      = $metadata->getMainKey();

        $pk = $this->entityToPk($pkOrEntity);

        return $this->getORM()
            ->select('n.*')
            ->from(
                [
                    [$metadata->getClassName(), 'n'],
                    [$metadata->getClassName(), 'p'],
                ]
            )
            ->where('n.lft', 'between', ['p.lft', 'p.rgt'])
            ->where('p.' . $key, '=', $pk)
            ->order('n.lft')
            ->all($metadata->getClassName());
    }

    public function isLeaf(mixed $pkOrEntity): bool
    {
        ArgumentsAssert::assert(
            is_object($pkOrEntity) || is_scalar($pkOrEntity),
            '{caller} conditions should be object or scalar, {value} given',
            $pkOrEntity
        );

        $pk = $this->entityToPk($pkOrEntity);

        $node = $this->getNode($pk);

        if ($node === null) {
            return false;
        }

        return ($node->rgt - $node->lft) === 1;
    }

    private function entityToPk(mixed $entity): mixed
    {
        $metadata = $this->getMetadata();

        if (is_object($entity) && $metadata::isEntity($entity)) {
            return $this->extract($entity)[$this->getMainKey()];
        }

        return $entity;
    }

    public function setPosition(
        NestedEntityInterface $entity,
        mixed $referenceId,
        int $position = Nested\Position::AFTER
    ): void {
        $allow = [
            Nested\Position::AFTER,
            Nested\Position::BEFORE,
            Nested\Position::FIRST_CHILD,
            Nested\Position::LAST_CHILD,
        ];

        // Make sure the location is valid.
        ArgumentsAssert::assert(
            !in_array($position, $allow, true),
            '{caller} position: {value} is invalid.',
            $position
        );

        $entity->getPosition()
            ->setReferenceId($referenceId)
            ->setPosition($position);
    }

    protected function getNode(mixed $value, ?string $key = null): ?Collection
    {
        $metadata = $this->getMetadata();

        // Determine which key to get the node base on.
        $k = match ($key) {
            static::PARENT => 'parent_id',
            static::LEFT => 'lft',
            static::RIGHT => 'rgt',
            default => $this->getMainKey(),
        };

        // Get the node data.
        $row = $this->getORM()
            ->select($this->getMainKey(), 'parent_id', 'level', 'lft', 'rgt')
            ->from($metadata->getClassName())
            ->where($k, '=', $value)
            ->limit(1)
            ->get();

        // Check for no $row returned
        if ($row === null) {
            throw new NestedHandleException(
                sprintf('%s::getNode(%d, %s) failed.', static::class, $value, $key)
            );
        }

        // Do some simple calculations.
        $row->numChildren = (int) ($row->rgt - $row->lft - 1) / 2;
        $row->width       = (int) $row->rgt - $row->lft + 1;

        return $row;
    }
}
