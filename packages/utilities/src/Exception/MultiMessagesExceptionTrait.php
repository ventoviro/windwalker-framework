<?php
/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Utilities\Exception;

use Throwable;

/**
 * The MultiMessagesExceptionTrait class.
 *
 * @since  3.0
 */
trait MultiMessagesExceptionTrait
{
    /**
     * Property messages.
     *
     * @var  array
     */
    protected array $messages = [];

    /**
     * Class init.
     *
     * @param  string|array|null  $messages
     * @param  int                $code
     * @param  Throwable|null    $previous
     */
    public function __construct(string|array $messages = null, int $code = 0, ?Throwable $previous = null)
    {
        $this->messages = (array) $messages;

        foreach ($this->messages as &$msgs) {
            $msgs = implode(PHP_EOL, (array) $msgs);
        }

        unset($msgs);

        parent::__construct(implode(PHP_EOL, (array) $this->messages), $code, $previous);
    }

    /**
     * Method to get property Messages
     *
     * @return  array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Method to set property messages
     *
     * @param  array  $messages
     *
     * @return  static  Return self to support chaining.
     */
    public function setMessages(array $messages): static
    {
        $this->messages = $messages;

        return $this;
    }
}
