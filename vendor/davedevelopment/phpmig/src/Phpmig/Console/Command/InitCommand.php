<?php
/**
 * @package    Phpmig
 * @subpackage Phpmig\Console
 */
namespace Phpmig\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This file is part of phpmig
 *
 * Copyright (c) 2011 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Init command
 *
 * @author      Dave Marshall <david.marshall@bskyb.com>
 */
class InitCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('init')
             ->setDescription('Initialise this directory for use with phpmig')
             ->setHelp(<<<EOT
The <info>init</info> command creates a skeleton bootstrap file and a migrations directory

<info>phpmig init</info>

EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cwd = getcwd();
        $bootstrap = $cwd . DIRECTORY_SEPARATOR . 'phpmig.php';
        $relative = 'migrations';
        $migrations = $cwd . DIRECTORY_SEPARATOR . $relative;

        $this->initMigrationsDir($migrations, $output);
        $this->initBootstrap($bootstrap, $relative, $output);
    }

    /**
     * Create migrations dir
     *
     * @param $path
     * @return void
     */
    protected function initMigrationsDir($migrations, OutputInterface $output)
    {
        if (file_exists($migrations) && is_dir($migrations)) {
            $output->writeln(
                '<info>--</info> ' .
                str_replace(getcwd(), '.', $migrations) . ' already exists -' .
                ' <comment>Place your migration files in here</comment>'
            );
            return;
        }

        if (false === mkdir($migrations)) {
            throw new \RuntimeException(sprintf('Could not create directory "%s"', $migrations));
        }

        $output->writeln(
            '<info>+d</info> ' .
            str_replace(getcwd(), '.', $migrations) .
            ' <comment>Place your migration files in here</comment>'
        );
    }

    /**
     * Create bootstrap
     *
     * @param string $bootstrap where to put bootstrap file
     * @param string $migrations path to migrations dir relative to bootstrap
     * @return void
     */
    protected function initBootstrap($bootstrap, $migrations, OutputInterface $output)
    {
        if (file_exists($bootstrap)) {
            $output->writeln(
                '<info>--</info> ' .
                str_replace(getcwd(), '.', $bootstrap) . ' already exists -' .
                ' <comment>Create services in here</comment>'
            );
            return;
        }

        if (!is_writeable(dirname($bootstrap))) {
            throw new \RuntimeException(sprintf('The file "%s" is not writeable', $bootstrap));
        }

        $contents = <<<PHP
<?php

use \Phpmig\Adapter;

\$container = new ArrayObject();

// replace this with a better Phpmig\Adapter\AdapterInterface
\$container['phpmig.adapter'] = new Adapter\File\Flat(__DIR__ . DIRECTORY_SEPARATOR . '$migrations/.migrations.log');

\$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

// You can also provide an array of migration files
// \$container['phpmig.migrations'] = array_merge(
//     glob('migrations_1/*.php'),
//     glob('migrations_2/*.php')
// );

return \$container;
PHP;

        if (false === file_put_contents($bootstrap, $contents)) {
            throw new \RuntimeException('The file "%s" could not be written to', $bootstrap);
        }

        $output->writeln(
            '<info>+f</info> ' .
            str_replace(getcwd(), '.', $bootstrap) .
            ' <comment>Create services in here</comment>'
        );
    }
}



