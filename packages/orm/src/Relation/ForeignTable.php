<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

use LogicException;

/**
 * The ForeignTable class.
 */
class ForeignTable
{
    /**
     * ForeignTable constructor.
     *
     * @param  string|null  $name
     * @param  array        $fks
     * @param  array        $morphs
     */
    public function __construct(
        protected ?string $name = null,
        protected array $fks = [],
        protected array $morphs = []
    ) {
        //
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param  string|null  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getFks(): array
    {
        return $this->fks;
    }

    /**
     * @param  array  $fks
     *
     * @return  static  Return self to support chaining.
     */
    public function setFks(array $fks): static
    {
        $this->fks = $fks;

        $this->checkMorphConflict();

        return $this;
    }

    /**
     * @return array
     */
    public function getMorphs(): array
    {
        return $this->morphs;
    }

    /**
     * @param  array  $morphs
     *
     * @return  static  Return self to support chaining.
     */
    public function setMorphs(array $morphs): static
    {
        $this->morphs = $morphs;

        $this->checkMorphConflict();

        return $this;
    }

    public function checkMorphConflict(): void
    {
        $conflict = array_intersect(array_keys($this->morphs), $this->fks);

        if ($conflict !== []) {
            throw new LogicException(
                sprintf(
                    'Morph key nad Foreign key conflict: (%s).',
                    implode(',', $conflict)
                )
            );
        }
    }
}
