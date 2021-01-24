<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use Windwalker\Edge\Compiler\EdgeCompiler;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;

error_reporting(-1);

include_once __DIR__ . '/../../../vendor/autoload.php';

$compiler = new EdgeCompiler();

//$compiler->addExtension(new \Windwalker\Edge\Extension\BasicExtension);

//echo $compiler->compile(file_get_contents(__DIR__ . '/tmpl.edge.php'));

$edge = new Edge(
    new EdgeFileLoader(
        [
            __DIR__,
        ]
    )
);

echo $edge->render('tmpl.components.main');
