<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Throwable;
use Windwalker\Cache\Exception\InvalidArgumentException;

/**
 * Class CacheItem
 *
 * @since 2.0
 */
class CacheItem implements CacheItemInterface
{
    use LoggerAwareTrait;

    protected ?string $key = null;

    /**
     * @var mixed
     */
    protected $value;

    protected bool $hit = false;

    protected ?DateTimeInterface $expiration = null;

    /**
     * Property defaultExpiration.
     *
     * @var  string
     */
    protected string $defaultExpiration = 'now +1 year';

    /**
     * Class constructor.
     *
     * @param  string|null  $key    The key for the cache item.
     * @param  mixed        $value  The value to cache.
     *
     * @since   2.0
     */
    public function __construct(?string $key = null, $value = null)
    {
        $this->validateKey($key);

        $this->key = $key;
        $this->logger = new NullLogger();

        if ($value !== null) {
            $this->set($value);
        }

        $this->expiresAfter(null);
    }

    /**
     * Get the key associated with this CacheItem.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Obtain the value of this cache item.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function get(): mixed
    {
        if ($this->isHit() === false) {
            return null;
        }

        return $this->value;
    }

    /**
     * Set the value of the item.
     *
     * If the value is set, we are assuming that there was a valid hit on the cache for the given key.
     *
     * @param  mixed  $value  The value for the cache item.
     *
     * @return  static
     */
    public function set(mixed $value): static
    {
        if ($this->key === null) {
            return $this;
        }

        $this->value = $value;
        $this->hit = true;

        return $this;
    }

    /**
     * This boolean value tells us if our cache item is currently in the cache or not.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isHit(): bool
    {
        try {
            if (new DateTime() > $this->expiration) {
                $this->hit = false;
            }

            return $this->hit;
        } catch (Throwable $e) {
            $this->logException(
                'CacheItem::isHit() caused an error',
                $e
            );

            return false;
        }
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param  ?DateTimeInterface  $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAt(?DateTimeInterface $expiration): static
    {
        try {
            if ($expiration instanceof DateTimeInterface) {
                $this->expiration = $expiration;
            } elseif ($expiration === null) {
                $this->expiration = new DateTime($this->defaultExpiration);
            } else {
                throw new \InvalidArgumentException('Invalid DateTime format.');
            }
        } catch (Throwable $e) {
            $this->logException(
                'CacheItem::expiresAt() causes an error',
                $e
            );
        }

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param  DateInterval|int|null  $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter(DateInterval|int|null $time): static
    {
        try {
            if ($time instanceof DateInterval) {
                $this->expiration = new DateTime();
                $this->expiration->add($time);
            } elseif (is_numeric($time)) {
                $this->expiration = new DateTime();
                $this->expiration->add(new DateInterval('PT' . $time . 'S'));
            } elseif ($time === null) {
                $this->expiration = new DateTime($this->defaultExpiration);
            } else {
                throw new InvalidArgumentException('Invalid DateTime format.');
            }
        } catch (Throwable $e) {
            $this->logException(
                'CacheItem::expiresAfter() caused an error.',
                $e
            );
        }

        return $this;
    }

    /**
     * Method to get property Expiration
     *
     * @return  DateTimeInterface
     */
    public function getExpiration(): DateTimeInterface
    {
        return $this->expiration;
    }

    /**
     * Method to set property hit
     *
     * @param  bool  $hit
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setIsHit(bool $hit): static
    {
        $this->hit = $hit;

        return $this;
    }

    /**
     * validateKey
     *
     * @param  string  $key
     *
     * @return  void
     *
     * @throws InvalidArgumentException
     */
    private function validateKey(string $key): void
    {
        if (strpbrk($key, '{}()/\@:')) {
            throw new InvalidArgumentException('Item key name contains reserved characters.' . $key);
        }
    }

    /**
     * logException
     *
     * @param  string     $message
     * @param  Throwable  $e
     *
     * @return  void
     */
    protected function logException(string $message, Throwable $e): void
    {
        $this->logger->critical(
            $message,
            [
                'exception' => $e,
                'key' => $this->getKey(),
            ]
        );
    }
}
