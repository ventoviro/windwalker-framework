<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Event\Attributes;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\Event\Listener\ListenerPriority;
use Windwalker\Event\Provider\SubscribableListenerProviderInterface;
use Windwalker\Utilities\Assert\Assert;

use function Windwalker\disposable;

/**
 * The ListenTo class.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE)]
class ListenTo
{
    /**
     * ListenTo constructor.
     *
     * @param  string    $event
     * @param  int|null  $priority
     * @param  bool      $once
     */
    public function __construct(
        public string $event,
        public ?int $priority = ListenerPriority::NORMAL,
        public bool $once = false,
    ) {
        //
    }
}
