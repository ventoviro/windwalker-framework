<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Bridge;

use Windwalker\Data\Format\FormatInterface;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Handler\NativeHandler;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The ArrayBridge class.
 */
class PhpBridge implements BridgeInterface
{
    use OptionAccessTrait;

    protected ?string $id = null;

    protected ?string $name = null;

    protected int $status = PHP_SESSION_NONE;

    protected array $storage = [];

    protected ?string $origin = null;

    protected ?HandlerInterface $handler = null;

    protected ?FormatInterface $serializer = null;

    /**
     * NativeBridge constructor.
     *
     * @see https://gist.github.com/franksacco/d6e943c41189f8ee306c182bf8f07654
     *
     * @param  array                  $options
     * @param  HandlerInterface|null  $handler
     * @param  FormatInterface|null   $serializer
     */
    public function __construct(
        array $options = [],
        HandlerInterface $handler = null,
        ?FormatInterface $serializer = null
    ) {
        $this->handler    = $handler ?? new NativeHandler();
        $this->serializer = $serializer;

        $this->prepareOptions(
            [
                static::OPTION_AUTO_COMMIT => false,
                static::OPTION_WITH_SUPER_GLOBAL => false,
            ],
            $options
        );
    }

    /**
     * start
     *
     * @return  bool
     */
    public function start(): bool
    {
        $this->handler->open($this->getOptionAndINI('save_path'), $this->getSessionName());

        if ($this->getOption('auto_commit')) {
            register_shutdown_function([$this, 'writeClose']);
        }

        $id = $this->getId();

        if (
            $id === null
            || (
                $this->getOptionAndINI('use_strict_mode')
                && $this->handler instanceof \SessionUpdateTimestampHandlerInterface
                && !$this->handler->validateId($id)
            )
        ) {
            $this->setId($id = $this->createId());
        }

        $this->origin = $dataString = $this->handler->read($id) ?: '';

        $this->storage = $this->decodeData($dataString);

        if ($this->getOption(static::OPTION_WITH_SUPER_GLOBAL)) {
            $_SESSION = &$this->storage;
        }

        $this->status = PHP_SESSION_ACTIVE;

        return true;
    }

    /**
     * isStarted
     *
     * @return  bool
     */
    public function isStarted(): bool
    {
        return $this->status === PHP_SESSION_ACTIVE;
    }

    /**
     * getId
     *
     * @return  string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * setId
     *
     * @param  string  $id
     *
     * @return  void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * getSessionName
     *
     * @return  string|null
     */
    public function getSessionName(): ?string
    {
        return $this->name ??= session_name();
    }

    /**
     * setSessionName
     *
     * @param  string  $name
     *
     * @return  void
     */
    public function setSessionName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * regenerate
     *
     * @param  bool  $deleteOld
     *
     * @param  bool  $saveOld
     *
     * @return  bool
     * @throws \Exception
     */
    public function regenerate(bool $deleteOld = false, bool $saveOld = true): bool
    {
        $this->origin = $data = $this->encodeData($this->getStorage());

        if ($deleteOld) {
            $this->handler->destroy($this->getId());
        } elseif ($saveOld) {
            $this->handler->write($this->getId(), $data);
        }

        $this->handler->close();
        $this->handler->open($this->getOptionAndINI('save_path'), $this->getSessionName());

        $this->setId($this->createId());

        $this->handler->write($this->getId(), $data);

        return true;
    }

    /**
     * writeClose
     *
     * @param  bool  $unset
     *
     * @return  bool
     */
    public function writeClose(bool $unset = true): bool
    {
        if ($this->status !== PHP_SESSION_ACTIVE) {
            return true;
        }

        if ($this->gcEnabled()) {
            $this->handler->gc($this->getOptionAndINI('gc_maxlifetime') ?? 1440);
        }

        $data = $this->encodeData($this->storage);

        if (
            $this->getOptionAndINI('lazy_write')
            && $this->origin === $data
            && $this->handler instanceof \SessionUpdateTimestampHandlerInterface
        ) {
            $r = $this->handler->updateTimestamp($this->getId(), $data);
        } else {
            $r = $this->handler->write($this->getId(), $data);
        }

        if ($unset) {
            $this->unset();
        }

        $this->status = PHP_SESSION_NONE;

        return $r;
    }

    /**
     * Close without write, only for Session::fork().
     *
     * @return  bool
     */
    public function close(): bool
    {
        $this->status = PHP_SESSION_NONE;

        return true;
    }

    public function gcEnabled(): bool
    {
        $probability = (int) $this->getOptionAndINI('gc_probability');
        $divisor     = (int) $this->getOptionAndINI('gc_divisor');

        if ($probability === 0 || $divisor === 0) {
            return false;
        }

        return random_int(1, $divisor) <= $probability;
    }

    /**
     * destroy
     *
     * @return  void
     */
    public function destroy(): void
    {
        if ($this->getId()) {
            $this->handler->destroy($this->getId());

            $this->unset();
        }

        $this->status = PHP_SESSION_NONE;
    }

    /**
     * @param  array  $storage
     *
     * @return  static  Return self to support chaining.
     */
    public function setStorage(array $storage): static
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * getStorage
     *
     * @return  array|null
     */
    public function &getStorage(): ?array
    {
        return $this->storage;
    }

    /**
     * generateId
     *
     * @return  string
     *
     * @throws \Exception
     */
    protected function createId(): string
    {
        if ($this->handler instanceof \SessionIdInterface) {
            return $this->handler->create_sid();
        }

        return session_create_id();
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * unset
     *
     * @return  bool
     */
    public function unset(): bool
    {
        $this->storage = [];

        if ($this->getOption(static::OPTION_WITH_SUPER_GLOBAL)) {
            unset($_SESSION);
        }

        return true;
    }

    protected function getOptionAndINI(string $name)
    {
        return $this->getOption($name) ?? ini_get('session.' . $name);
    }

    /**
     * @return HandlerInterface
     */
    public function getHandler(): HandlerInterface
    {
        return $this->handler;
    }

    /**
     * @param  HandlerInterface  $handler
     *
     * @return  static  Return self to support chaining.
     */
    public function setHandler(HandlerInterface $handler): static
    {
        if ($this->getStatus() === PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Cannot change handler during session active.');
        }

        $this->handler = $handler;

        return $this;
    }

    public function getSerializer(): ?FormatInterface
    {
        return $this->serializer;
    }

    public function setSerializer(?FormatInterface $serializer): static
    {
        if ($this->getStatus() === PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Cannot change serializer during session active.');
        }

        $this->serializer = $serializer;

        return $this;
    }

    protected function decodeData(string $dataString): array
    {
        if (!$this->serializer) {
            return (array) (unserialize($dataString) ?: []);
        }

        return (array) ($this->serializer->parse($dataString) ?: []);
    }

    protected function encodeData(array $storage): string
    {
        if (!$this->serializer) {
            return serialize($storage);
        }

        return $this->serializer->dump($storage);
    }

    /**
     * __clone
     *
     * @return  void
     */
    public function __clone()
    {
        $this->handler = clone $this->handler;

        if ($this->serializer) {
            $this->serializer = clone $this->serializer;
        }
    }
}
