<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later. see LICENSE
 */

use Asika\SimpleConsole\Console;
use Symfony\Component\Process\Process;

include __DIR__ . '/../vendor/autoload.php';

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
        'authentication' => 'Authentication',
        'authorisation' => 'Authorisation',
        'cache' => 'Cache',
        'crypt' => 'Crypt',
        'data' => 'Data',
        'database' => 'Database',
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
        'language' => 'Language',
        'query' => 'Query',
        'queue' => 'Queue',
        'renderer' => 'Renderer',
        'scalars' => 'Scalars',
        'session' => 'Session',
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
        foreach ($this->subtrees as $subtree => $class) {
            $tmp = __DIR__ . '/../tmp/git';

            $this->runProcess(
                "git clone git@github.com:ventoviro/windwalker-$subtree.git $subtree",
                $tmp
            );

            $tmp = $tmp . '/' . $subtree;

            // $this->runProcess("git branch 3.x", $tmp);
            // $this->runProcess("git checkout 3.x", $tmp);
            //
            // $this->runProcess("git push origin 3.x", $tmp);

            $this->runProcess("git checkout --orphan 4.x", $tmp);

            $this->runProcess("git reset", $tmp);

            $this->runProcess("git add composer.json", $tmp);

            $this->runProcess("git commit -am \"4.x init\"", $tmp);

            $this->runProcess("git reset HEAD --hard", $tmp);

            // $this->runProcess("git push origin master", $tmp);

            exit(' @Checkpoint');
        }

        return true;
    }

    protected function runProcess($cmd, $cwd): int
    {
        $this->out()->out('>> ' . $cmd)->out();

        $proc = Process::fromShellCommandline($cmd, $cwd);
        return $proc->run([$this, 'print']);
    }

    public function print($type, $buffer)
    {
        if (Process::ERR === $type) {
            $this->out($buffer, false);
        } else {
            $this->err($buffer, false);
        }
    }


    /**
     * Exec a command.
     *
     * @param string $command
     * @param array  $arguments
     * @param array  $options
     *
     * @return  string
     */
    protected function exec($command, $arguments = [], $options = []): string
    {
        $arguments = implode(' ', (array) $arguments);
        $options   = implode(' ', (array) $options);

        $command = sprintf('%s %s %s', $command, $arguments, $options);

        $this->out('>> ' . $command);

        if ($this->getOption('dry-run')) {
            return '';
        }

        $return = exec(trim($command), $this->lastOutput, $this->lastReturn);

        $this->out($return);
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
     * @param string $msg
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
}

$app = new Build();

$app->execute();
