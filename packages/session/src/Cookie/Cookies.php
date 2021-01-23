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
 * The CookieSetter class.
 */
class Cookies implements CookiesInterface
{
    public const SAMESITE_NONE = 'None';
    public const SAMESITE_LAX = 'Lax';
    public const SAMESITE_STRICT = 'Strict';

    protected int $expires = 0;
    protected string $path;
    protected string $domain;
    protected bool $secure = false;
    protected bool $httpOnly = false;
    protected string $sameSite = self::SAMESITE_LAX;

    public static function create(): static
    {
        return new static();
    }

    public function set(string $name, string $value): bool
    {
        if (headers_sent()) {
            throw new \RuntimeException('Header sent');
        }

        return setcookie($name, $value, $this->getOptions());
    }

    public function get(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    public function remove(string $name): bool
    {
        return setcookie($name, '', ['expires' => time() - 500]);
    }

    public function getOptions(): array
    {
        return array_change_key_case(get_object_vars($this), CASE_LOWER);
    }

    public function setOptions(array $options): static
    {
        $this->expires = $options['expires'] ?? $this->expires;
        $this->path = $options['path'] ?? $this->path;
        $this->domain = $options['domain'] ?? $this->domain;
        $this->secure = $options['secure'] ?? $this->secure;
        $this->httpOnly = $options['httponly'] ?? $this->httpOnly;
        $this->sameSite = $options['samesite'] ?? $this->sameSite;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     * @param  \DateTimeInterface|int|string  $expires  Timestamp, DateTime or time modify string.
     *
     * @return static Return self to support chaining.
     */
    public function expires(int|string|\DateTimeInterface $expires): static
    {
        if (is_string($expires) && !is_numeric($expires)) {
            $date = new \DateTimeImmutable('now');
            $expires = $date->modify($expires);
        }

        if ($expires instanceof \DateTimeInterface) {
            $expires = $expires->getTimestamp();
        }

        $this->expires = (int) $expires;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     */
    public function path(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param  string  $domain
     *
     * @return  static  Return self to support chaining.
     */
    public function domain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param  bool  $secure
     *
     * @return  static  Return self to support chaining.
     */
    public function secure(bool $secure): static
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @param  bool  $httpOnly
     *
     * @return  static  Return self to support chaining.
     */
    public function httpOnly(bool $httpOnly): static
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * @return string
     */
    public function isSameSite(): string
    {
        return $this->sameSite;
    }

    /**
     * @param  string  $sameSite
     *
     * @return  static  Return self to support chaining.
     */
    public function sameSite(string $sameSite): static
    {
        $this->sameSite = $sameSite;

        return $this;
    }
}
