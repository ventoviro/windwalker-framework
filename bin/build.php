<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later. see LICENSE
 */

use Asika\SimpleConsole\Console;

include_once __DIR__ . '/Console.php';

/**
 * Class Build to build subtrees.
 *
 * @since 1.0
 */
class Build extends Console
{
    /**
     * Property organization.
     *
     * @var  string
     */
    protected $organization = 'ventoviro';

    /**
     * Property lastOutput.
     *
     * @var  mixed
     */
    protected $lastOutput = null;

    /**
     * Property lastReturn.
     *
     * @var  mixed
     */
    protected $lastReturn = null;

    /**
     * Property master.
     *
     * @var  string
     */
    protected $branch = null;

    /**
     * Property tag.
     *
     * @var  string
     */
    protected $tag = null;

    /**
     * Property subtrees.
     *
     * @var  array
     */
    protected $subtrees = [
        'attributes' => 'Attributes',
        'authentication' => 'Authentication',
        'authorisation' => 'Authorisation',
        'cache' => 'Cache',
        'crypt' => 'Crypt',
        'data' => 'Data',
        'database' => 'Database',
        // 'datamapper' => 'DataMapper',
        'di' => 'DI',
        'dom' => 'Dom',
        'edge' => 'Edge',
        'environment' => 'Environment',
        'event' => 'Event',
        'filesystem' => 'Filesystem',
        'filter' => 'Filter',
        'form' => 'Form',
        'html' => 'Html',
        'http' => 'Http',
        'promise' => 'Promise',
        'language' => 'Language',
        'orm' => 'ORM',
        'pool' => 'Pool',
        'query' => 'Query',
        'queue' => 'Queue',
        'reactor' => 'Reactor',
        'renderer' => 'Renderer',
        'scalars' => 'Scalars',
        'session' => 'Session',
        'stream' => 'Stream',
        'test' => 'Test',
        'uri' => 'Uri',
        'utilities' => 'Utilities',
        'validator' => 'Validator',
    ];

    /**
     * Method to run this application.
     *
     * @return  boolean
     */
    protected function doExecute(): bool
    {
        if ($this->getOption('h')) {
            return $this->help();
        }

        $this->tag = $tag = $this->getOption('t') ?: $this->getOption('tag');

        if ($this->tag === 'master') {
            throw new \RuntimeException('Do not use master now');
        }

        $branch = $this->getOption('b') ?: $this->getOption('branch', 'test');

        $force = $this->getOption('f') ?: $this->getOption('force', false);

        $force = $force ? ' -f' : false;

        // if ($this->tag && !$this->getOption('no-replace')) {
        //     $this->replaceDocblockTags();
        // }

        $this->branch = $branch;

        $this->exec('git fetch origin');

        $this->exec('git branch -D ' . $branch);

        $this->exec('git checkout -b ' . $branch);

        // $this->exec('git merge master');

        if ($this->tag) {
            $this->exec('git tag -d ' . $tag);

            $this->exec('git push origin :refs/tags/' . $tag);

            $this->exec('git tag ' . $tag);

            $this->exec(sprintf('git push origin %s' . $force, $this->tag));
        }

        $this->exec(sprintf('git push origin %s:%s' . $force, $branch, $branch));
        // $this->exec(sprintf('git push origin %s %s:%s master:master' . $force, $tag, $branch, $branch));

        $allows = $this->args;

        foreach ($this->subtrees as $subtree => $namespace) {
            if ($allows && !in_array($subtree, $allows)) {
                continue;
            }

            if (!is_dir(__DIR__ . '/../packages/' . $subtree)) {
                continue;
            }

            $this->splitTree($subtree, $namespace);
        }

        $this->exec('git checkout ' . $branch);

        $this->out()->out('Split finish.');

        return true;
    }

    /**
     * Split Git subTree.
     *
     * @param  string  $subtree
     * @param  string  $namespace
     *
     * @return  void
     */
    protected function splitTree($subtree, $namespace)
    {
        $this->out()->out(sprintf('@ Start subtree split (%s)', $subtree))
            ->out('---------------------------------------');

        // Do split
        $this->exec(sprintf('git branch -D sub-%s', $subtree));
        $this->exec('git subtree split -P packages/' . $subtree . ' -b sub-' . $subtree);

        // Create a new branch
        $this->exec(sprintf('git branch -D %s-%s', $this->branch, $subtree));

        // Add remote repo
        $repo = sprintf(
            'git@github.com:%s/windwalker-%s.git',
            $this->organization,
            $subtree
        );

        $force = $this->getOption('f') ?: $this->getOption('force', false);

        $force = $force ? ' -f' : false;

        if (!$force) {
            $this->exec(
                sprintf(
                    'git pull %s %s',
                    $repo,
                    $this->branch
                )
            );
        }

        $this->exec(sprintf('git push %s sub-%s:%s ' . $force, $repo, $subtree, $this->branch));

        if ($this->tag) {
            $this->exec('git checkout sub-' . $subtree);

            $this->exec(sprintf('git tag -d %s', $this->tag));

            $this->exec(sprintf('git push %s :refs/tags/%s', $subtree, $this->tag));

            $this->exec(sprintf('git tag %s', $this->tag));

            $this->exec(sprintf('git push %s %s', $subtree, $this->tag));
        }

        $this->exec('git checkout ' . $this->branch);
        $this->exec('git branch -D sub-' . $subtree);
    }

    /**
     * Exec a command.
     *
     * @param  string  $command
     * @param  array   $arguments
     * @param  array   $options
     *
     * @return  static
     */
    protected function exec($command, $arguments = [], $options = []): static
    {
        $arguments = implode(' ', (array) $arguments);
        $options   = implode(' ', (array) $options);

        $command = sprintf('%s %s %s', $command, $arguments, $options);

        $this->out('>> ' . $command);

        if ($this->getOption('dry-run')) {
            return $this;
        }

        $return = exec(trim($command), $this->lastOutput, $this->lastReturn);

        $this->out($return);

        return $this;
    }

    /**
     * help
     *
     * @return  boolean
     */
    protected function help(): bool
    {
        $help = <<<HELP
Windwalker Build Command.

Will run subtree split and push every packages to it's repos.

Usage: php build.php [packages] [-t] [-b=test] [-f] [--dry-run] [--no-replace]

-t | --tags     Git tag of this build, will push to main repo and every subtree.
-b | --branch   Get branch to push, will  push to main repo and every subtree.
-f | --force    Override commits or not.
--dry-run       Do not real push, just run the subtree split process.
--no-replace    Do not replace the docblock variables.

HELP;

        $this->out($help);

        return true;
    }

    /**
     * stop
     *
     * @param  string  $msg
     *
     * @return  void
     */
    protected function stop($msg = null)
    {
        if ($msg) {
            $this->out($msg);
        }

        $this->close();
    }

    /**
     * replaceDocblockTags
     *
     * @return  void
     */
    protected function replaceDocblockTags()
    {
        $this->out('Replacing Docblock');

        $files = new RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                WINDWALKER_ROOT . '/src',
                \FilesystemIterator::SKIP_DOTS
            )
        );

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() != 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            $content = str_replace(
                ['{DEPLOY_VERSION}', '__DEPLOY_VERSION__', '__LICENSE__', '{ORGANIZATION}'],
                [$this->tag, $this->tag, 'LGPL-2.0-or-later', 'Asikart'],
                $content
            );

            file_put_contents($file->getPathname(), $content);
        }

        $this->exec('git checkout master');
        $this->exec(sprintf('git commit -am "Prepare for %s release."', $this->tag));
        $this->exec('git push origin master');
    }
}

$app = new Build();

$app->execute();
