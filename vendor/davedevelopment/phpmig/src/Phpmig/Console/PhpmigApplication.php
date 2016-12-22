<?php
/**
 * @package    Phpmig
 * @subpackage Console
 */
namespace Phpmig\Console;

use Phpmig\Console\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * This file is part of phpmig
 *
 * Copyright (c) 2011 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The main phpmig application
 *
 * @author      Dave Marshall <david.marshall@bskyb.com>
 */
class PhpmigApplication extends Application
{
    /**
     * @param string $version
     */
    public function __construct($version = 'dev')
    {
        parent::__construct('phpmig', $version);

        $this->addCommands(array(
            new Command\InitCommand(),
            new Command\StatusCommand(),
            new Command\CheckCommand(),
            new Command\GenerateCommand(),
            new Command\UpCommand(),
            new Command\DownCommand(),
            new Command\MigrateCommand(),
            new Command\RollbackCommand(),
            new Command\RedoCommand()
        ));
    }
}

