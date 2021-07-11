<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use Windwalker\Edge\Cache\EdgeArrayCache;
use Windwalker\Edge\Compiler\EdgeCompiler;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;

include_once __DIR__ . '/../../../../vendor/autoload.php';

ini_set('memory_limit', '128M');

$finder = new EdgeFileLoader();

$finder->addPath(__DIR__ . '/edge');

$edge = new Edge($finder, new EdgeCompiler(), new EdgeArrayCache());

//$edge->addExtension(new \Windwalker\Edge\Extension\BasicExtension);

echo $edge->render('hello');
