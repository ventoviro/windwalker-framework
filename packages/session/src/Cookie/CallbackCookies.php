<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

/**
 * The CallbackCookies class.
 */
class CallbackCookies implements CookiesInterface
{
    /**
     * @var callable
     */
    protected $getter;

    /**
     * @var callable
     */
    protected $setter;

    /**
     * CallbackCookies constructor.
     *
     * @param  callable|null  $getter
     * @param  callable|null  $setter
     */
    public function __construct(?callable $getter = null, ?callable $setter = null)
    {
        $this->getter = $getter ?? fn() => true;
        $this->setter = $setter ?? fn() => null;
    }

    /**
     * set
     *
     * @param  string  $name
     * @param  string  $value
     *
     * @return  bool
     */
    public function set(string $name, string $value): bool
    {
        return (bool) $this->getSetter()($name, $value);
    }

    /**
     * get
     *
     * @param  string  $name
     *
     * @return  string|null
     */
    public function get(string $name): ?string
    {
        return $this->getGetter()($name);
    }

    /**
     * @return callable
     */
    public function getGetter(): callable
    {
        return $this->getter;
    }

    /**
     * @param  callable  $getter
     *
     * @return  static  Return self to support chaining.
     */
    public function setGetter(callable $getter): static
    {
        $this->getter = $getter;

        return $this;
    }

    /**
     * @return callable
     */
    public function getSetter(): callable
    {
        return $this->setter;
    }

    /**
     * @param  callable  $setter
     *
     * @return  static  Return self to support chaining.
     */
    public function setSetter(callable $setter): static
    {
        $this->setter = $setter;

        return $this;
    }
}
