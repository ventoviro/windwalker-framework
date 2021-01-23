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
 * Class AbstractHandler
 *
 * @since 2.0
 */
abstract class AbstractHandler implements HandlerInterface, \SessionUpdateTimestampHandlerInterface, \SessionIdInterface
{
    protected ?string $loadedData = null;

    protected bool $newSessionId = false;

    /**
     * Re-initializes existing session, or creates a new one.
     *
     * @param  string  $savePath     Save path
     * @param  string  $sessionName  Session name, see http://php.net/function.session-name.php
     *
     * @return bool true on success, false on failure
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * Closes the current session.
     *
     * @return bool true on success, false on failure
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * validateId
     *
     * @param  string  $id
     *
     * @return  bool
     */
    public function validateId($id): bool
    {
        $this->loadedData = $this->read($id);

        $newSessionId = $this->newSessionId;

        $this->newSessionId = false;

        return !$newSessionId;
    }

    /**
     * read
     *
     * @param  string  $id
     *
     * @return  string
     */
    public function read($id): string
    {
        $data = $this->loadedData;

        if ($data !== null) {
            $this->loadedData = null;
            return $data;
        }

        $data = $this->doRead($id);

        if ($data === null) {
            $this->newSessionId = true;
        }

        return (string) $data;
    }

    abstract protected function doRead(string $id): ?string;

    /**
     * Create session ID
     * @link https://php.net/manual/en/sessionidinterface.create-sid.php
     * @return string
     */
    public function create_sid(): string
    {
        return session_create_id();
    }
}
