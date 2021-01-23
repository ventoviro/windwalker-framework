<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Handler;

/**
 * The ArrayHandler class.
 */
class ArrayHandler extends AbstractHandler
{
    protected array $sessions = [];

    /**
     * ArrayHandler constructor.
     *
     * @param  array  $sessions
     */
    public function __construct(array $sessions = [])
    {
        $this->sessions = $sessions;
    }

    public static function createData(string $data): array
    {
        $time = time();

        return compact('data', 'time');
    }

    /**
     * doRead
     *
     * @param  string  $id
     *
     * @return  string|null
     */
    protected function doRead(string $id): ?string
    {
        return $this->sessions[$id]['data'] ?? null;
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * destroy
     *
     * @param  string  $id
     *
     * @return  bool
     */
    public function destroy($id): bool
    {
        unset($this->sessions[$id]);

        return true;
    }

    /**
     * gc
     *
     * @param  int  $lifetime
     *
     * @return  bool
     */
    public function gc($lifetime): bool
    {
        $past = time() - $lifetime;

        $this->sessions = array_filter($this->sessions, fn ($sess) => $sess['time'] >= $past);

        return true;
    }

    /**
     * write
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function write($id, $data): bool
    {
        $this->sessions[$id] = static::createData($data);

        return true;
    }

    /**
     * updateTimestamp
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function updateTimestamp($id, $data): bool
    {
        if (isset($this->sessions[$id])) {
            $this->sessions[$id]['time'] = time();
        }

        return true;
    }

    /**
     * @return array
     */
    public function getSessions(): array
    {
        return $this->sessions;
    }

    /**
     * @param  array  $sessions
     *
     * @return  static  Return self to support chaining.
     */
    public function setSessions(array $sessions): static
    {
        $this->sessions = $sessions;

        return $this;
    }
}
