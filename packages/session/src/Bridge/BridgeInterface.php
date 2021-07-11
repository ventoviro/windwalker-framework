<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Bridge;

use RuntimeException;

/**
 * Interface SessionBridgeInterface
 *
 * @since  2.0
 */
interface BridgeInterface
{
    public const OPTION_AUTO_COMMIT = 'auto_commit';

    public const OPTION_WITH_SUPER_GLOBAL = 'with_super_global';

    /**
     * Starts the session.
     *
     * @return  bool  True if started.
     *
     * @throws RuntimeException If something goes wrong starting the session.
     */
    public function start(): bool;

    /**
     * Checks if the session is started.
     *
     * @return  bool  True if started, false otherwise.
     */
    public function isStarted(): bool;

    /**
     * Returns the session ID
     *
     * @return string|null The session ID or empty.
     */
    public function getId(): ?string;

    /**
     * Sets the session ID
     *
     * @param  string  $id  Set the session id
     *
     * @return  void
     */
    public function setId(string $id): void;

    /**
     * Returns the session name
     *
     * @return string|null The session name.
     */
    public function getSessionName(): ?string;

    /**
     * Sets the session name
     *
     * @param  string  $name  Set the name of the session
     *
     * @return  void
     */
    public function setSessionName(string $name): void;

    /**
     * regenerate
     *
     * @param  bool  $deleteOld
     *
     * @param  bool  $saveOld
     *
     * @return  bool
     */
    public function regenerate(bool $deleteOld = false, bool $saveOld = true): bool;

    /**
     * Force the session to be saved and closed.
     *
     * This method must invoke session_write_close() unless this interface is
     * used for a storage object design for unit or functional testing where
     * a real PHP session would interfere with testing, in which case it
     * it should actually persist the session data if required.
     *
     * @param  bool  $unset
     *
     * @return bool
     *
     * @throws RuntimeException If the session is saved without being started, or if the session
     *                           is already closed.
     */
    public function writeClose(bool $unset = true): bool;

    /**
     * Clear all session data in memory.
     *
     * @return  void
     */
    public function destroy(): void;

    public function unset(): bool;

    // /**
    //  * getCookieParams
    //  *
    //  * @return  array
    //  */
    // public function getCookieParams(): array;
    //
    // /**
    //  * Set session cookie parameters, this method should call before session started.
    //  *
    //  * @param   integer $lifetime   Lifetime of the session cookie, defined in seconds.
    //  * @param   string  $path       Path on the domain where the cookie will work. Use a single
    //  *                              slash ('/') for all paths on the domain.
    //  * @param   string  $domain     Cookie domain, for example 'www.php.net'. To make cookies
    //  *                              visible on all sub domains then the domain must be prefixed
    //  *                              with a dot like '.php.net'.
    //  * @param   boolean $secure     If true cookie will only be sent over secure connections.
    //  * @param   boolean $httponly   If set to true then PHP will attempt to send the httponly
    //  *                              flag when setting the session cookie.
    //  *
    //  * @return  static
    //  *
    //  * @since   2.0
    //  */
    // public function setCookieParams($lifetime, $path = null, $domain = null, $secure = false, $httponly = true);

    /**
     * getStorage
     *
     * @return array|null
     */
    public function &getStorage(): ?array;

    /**
     * @return int
     */
    public function getStatus(): int;
}
