<?php

/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\Data\Collection;

include __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/Console.php';

define('PROJECT_ROOT', realpath(dirname(__DIR__)));
define('PACKAGES_PATH', PROJECT_ROOT . '/packages');

class MergeComposer extends \Asika\SimpleConsole\Console
{
    /**
     * doExecute
     *
     * @return  int
     */
    protected function doExecute(): int
    {
        $packages = \Windwalker\Filesystem\Filesystem::folders(PACKAGES_PATH);

        $rootJsonFile = PROJECT_ROOT . '/composer.json';
        $rootJson = Collection::from($rootJsonFile);

        $rootJson['replace'] = [];

        foreach ($packages as $package) {
            if (!is_file($composerFile = $package->getPathname() . '/composer.json')) {
                continue;
            }

            $json = Collection::from($composerFile);

            if (!$json->get('name')) {
                continue;
            }

            $this->mergeRequires($rootJson, $json, 'require');
            $this->mergeRequires($rootJson, $json, 'require-dev');
            $this->mergeRequires($rootJson, $json, 'suggest');

            $this->mergeAutoload($rootJson, $json, 'autoload.psr-4', 'src');
            $this->mergeAutoload($rootJson, $json, 'autoload.files', 'src/bootstrap.php');
            $this->mergeAutoload($rootJson, $json, 'autoload-dev.psr-4', 'test');
            $this->mergeReplace($rootJson, $json);
        }

        \Windwalker\Filesystem\Filesystem::write(
            $rootJsonFile,
            $rootJson->toJson(['options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES])
        );

        $this->out('Sync to composer.json');

        return 1;
    }

    protected function mergeRequires(Collection $rootJson, Collection $json, string $path)
    {
        $rootRequires = $rootJson->proxy($path);

        $requires = $json->getDeep($path);

        foreach ($requires as $package => $pkgVersion) {
            if (str_starts_with($package, 'windwalker')) {
                continue;
            }

            if (!$rootRequires[$package]) {
                $rootRequires[$package] = $pkgVersion;
                continue;
            }

            $rootVersion = explode('|', (string) $rootRequires[$package]);
            $rootVersion = $rootVersion[array_key_last($rootVersion)];
            $version = explode('|', (string) $pkgVersion);
            $version = $version[array_key_last($version)];

            if (\Composer\Semver\Comparator::greaterThan($version, $rootVersion)) {
                $rootRequires[$package] = $pkgVersion;
            } elseif (\Composer\Semver\Comparator::lessThan($version, $rootVersion) && str_starts_with($path, 'require')) {
                $this->out(
                    sprintf(
                        '[Warning] %s: %s in %s less than root %s.',
                        $package,
                        $pkgVersion,
                        $json->getDeep('name'),
                        $rootRequires[$package]
                    )
                );
            }
        }
    }

    protected function mergeAutoload(Collection $rootJson, Collection $json, string $path, string $dir)
    {
        $target = $rootJson->proxy($path);

        $name = explode('/', $json->get('name'))[1];

        foreach ((array) $json->getDeep($path) as $key => $item) {
            if (is_numeric($key)) {
                $target->append("packages/$name/$dir")
                    ->apply(fn ($storage) => array_unique($storage))
                    ->sort()
                    ->values();
            } else {
                $target->set($key, "packages/$name/$dir")
                    ->sortKeys();
            }
        }
    }

    protected function mergeReplace(Collection $rootJson, Collection $json)
    {
        $target = $rootJson->proxy('replace');
        $target[$json->get('name')] = 'self.version';
        $target->sortKeys();
    }
}

(new MergeComposer())->execute();
