<?php

namespace OAuth2\ServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateScopeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('OAuth2:CreateScope')
            ->setDescription('Create a scope for use in OAuth2')
            ->addArgument('scope', InputArgument::REQUIRED, 'The scope key/name')
            ->addArgument('description', InputArgument::REQUIRED, 'The scope description used on authorization screen')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $scopeManager = $container->get('oauth2.scope_manager');

        try {
            $scopeManager->createScope($input->getArgument('scope'), $input->getArgument('description'));
        } catch (\Doctrine\DBAL\DBALException $e) {
            $output->writeln('<fg=red>Unable to create scope ' . $input->getArgument('scope') . '</fg=red>');

            return;
        }

        $output->writeln('<fg=green>Scope ' . $input->getArgument('scope') . ' created</fg=green>');
    }
}
