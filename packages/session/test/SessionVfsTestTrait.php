<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Test;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use org\bovigo\vfs\visitor\vfsStreamVisitor;

/**
 * Trait SessionVfsTestTrait
 */
trait SessionVfsTestTrait
{
    protected static string $sess1 = '93cd6b3ec9f36b23d68e9385942dc41c';

    protected static string $sess2 = 'fa0a731220e28af75afba7135723015e';

    protected vfsStreamDirectory $root;

    protected static function getSessionPath(?string $path = null): string
    {
        return 'vfs://root/tmp' . ($path ? '/' . $path : '');
    }

    protected function prepareVfs(?array $structure = null): void
    {
        $this->root = vfsStream::setup(
            'root',
            null,
            $structure ?? [
                'tmp' => [
                    'sess_' . static::$sess1 => 'a:2:{s:6:"flower";s:6:"Sakura";s:6:"animal";s:3:"Cat";}',
                    'sess_' . static::$sess2 => 'a:2:{s:6:"flower";s:4:"Rose";s:4:"tree";s:3:"Oak";}',
                ],
            ]
        );
    }

    protected function inspectVfs(): vfsStreamVisitor
    {
        return vfsStream::inspect(new vfsStreamStructureVisitor());
    }
}
