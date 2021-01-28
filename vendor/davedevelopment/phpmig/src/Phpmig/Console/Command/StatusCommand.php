<?php
/**
 * @package    Phpmig
 * @subpackage Phpmig\Console
 */
namespace Phpmig\Console\Command;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * This file is part of phpmig
 *
 * Copyright (c) 2011 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Status command
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class StatusCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('status')
             ->setDescription('Show the up/down status of all migrations')
             ->setHelp(<<<EOT
The <info>status</info> command prints a list of all migrations, along with their current status 

<info>phpmig status</info>

EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);
        $output->writeln("");
        $output->writeln(" Status   Migration ID    Migration Name ");
        $output->writeln("-----------------------------------------");

        $versions = $this->getAdapter()->fetchAll();
        foreach($this->getMigrations() as $migration) {

            if (in_array($migration->getVersion(), $versions)) {
                $status = "     <info>up</info> ";
                unset($versions[array_search($migration->getVersion(), $versions)]);
            } else {
                $status = "   <error>down</error> ";
            }

            $output->writeln(
                $status .
                sprintf(" %14s ", $migration->getVersion()) .
                " <comment>" . $migration->getName() . "</comment>"
            );
        }

        foreach($versions as $missing) {
            $output->writeln(
                '   <error>up</error> ' .
                sprintf(" %14s ", $missing) .
                ' <error>** MISSING **</error> '
            );
        }

        // print status
        $output->writeln("");
        return 0;
    }
}



