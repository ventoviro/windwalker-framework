<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event;

use Serializable;
use Windwalker\Utilities\Accessible\AccessibleTrait;
use Windwalker\Utilities\Contract\AccessibleInterface;

/**
 * Class Event
 *
 * @since 2.0
 */
class Event extends AbstractEvent implements Serializable, AccessibleInterface
{
    use AccessibleTrait;

    /**
     * mergeArguments
     *
     * @param  array  $arguments
     *
     * @return  static
     */
    public function merge(array $arguments): static
    {
        foreach ($arguments as $key => &$value) {
            $this->storage[$key] = &$value;
        }

        return $this;
    }

    /**
     * getArguments
     *
     * @return  array
     */
    public function &getArguments(): array
    {
        return $this->storage;
    }

    /**
     * Clear all event arguments.
     *
     * @return  static  Return self to support chaining.
     *
     * @since   2.0
     */
    public function clear(): static
    {
        // Break the reference
        unset($this->storage);

        $this->storage = [];

        return $this;
    }

    /**
     * Serialize the event.
     *
     * @return  string  The serialized event.
     *
     * @since   2.0
     */
    public function serialize(): string
    {
        return serialize([$this->name, $this->storage, $this->stopped]);
    }

    /**
     * Unserialize the event.
     *
     * @param  string  $serialized  The serialized event.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function unserialize($serialized)
    {
        [$this->name, $this->storage, $this->stopped] = unserialize($serialized);
    }

    public function &__call(string $name, array $args)
    {
        if (str_starts_with(strtolower($name), 'get')) {
            $field = substr($name, 3);

            return $this->get(lcfirst($field));
        }

        if (str_starts_with(strtolower($name), 'set')) {
            $field = substr($name, 3);

            $this->set(lcfirst($field), ...$args);

            return $this;
        }

        return $this->$name(...$args);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'stopped' => $this->stopped,
            'arguments' => $this->storage,
        ];
    }
}
