<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Events;

/**
 * The MessageEventTrait class.
 */
trait MessageEventTrait
{
    protected string $message;
    protected string $messageType;

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param  string  $message
     *
     * @return  static  Return self to support chaining.
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }

    /**
     * @param  string  $messageType
     *
     * @return  static  Return self to support chaining.
     */
    public function setMessageType(string $messageType): static
    {
        $this->messageType = $messageType;

        return $this;
    }
}
