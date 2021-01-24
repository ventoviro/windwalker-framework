<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use React\EventLoop\StreamSelectLoop;
use Windwalker\Http\HttpClient;
use Windwalker\Http\Transport\SimpleAsyncTransport;
use Windwalker\Promise\Scheduler\EventLoopScheduler;
use Windwalker\Promise\Scheduler\ScheduleRunner;

$autoload = __DIR__ . '/../../vendor/autoload.php';

if (!is_file($autoload)) {
    $autoload = __DIR__ . '/../../../../vendor/autoload.php';
}

include $autoload;

ScheduleRunner::getInstance();

$loop = new StreamSelectLoop();
ScheduleRunner::getInstance()->setSchedulers([new EventLoopScheduler($loop)]);

$http = new HttpClient();
$http->setAsyncTransport(new SimpleAsyncTransport($http->getTransport()));
$p1 = $http->getAsync('https://github.com')
    ->then(
        function ($res) {
            show((string) $res->getBody());
        }
    );
$p2 = $http->getAsync('https://pravatar.cc/')
    ->then(
        function ($res) {
            show((string) $res->getBody());
        }
    );

$p2 = $p2->then(fn() => $loop->stop());
// $p1->wait();
$p2->wait();

$loop->run();
