<?php

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

class CheckCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('check')
             ->setDescription('Check all migrations have been run, exit with non-zero if not')
             ->setHelp(<<<EOT
The <info>check</info> checks that all migrations have been run and exits with a 
non-zero exit code if not, useful for build or deployment scripts.

<info>phpmig check</info>

EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);
        $versions = $this->getAdapter()->fetchAll();
        $down = array();
        foreach($this->getMigrations() as $migration) {
            if (!in_array($migration->getVersion(), $versions)) {
                $down[] = $migration;
            }
        }

        if (!empty($down)) {
            $output->writeln("");
            $output->writeln(" Status   Migration ID    Migration Name ");
            $output->writeln("-----------------------------------------");

            foreach ($down as $migration) {
                $output->writeln(
                    sprintf(
                        "   <error>down</error>  %14s  <comment>%s</comment>", 
                        $migration->getVersion(), 
                        $migration->getName()
                    )
                );
            }

            $output->writeln("");

            return 1;
        }

        return 0;
    }
}



