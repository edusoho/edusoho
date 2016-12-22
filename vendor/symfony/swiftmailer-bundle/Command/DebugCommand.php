<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SwiftmailerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * A console command for retrieving information about mailers
 *
 * @author Jérémy Romey <jeremy@free-agent.fr>
 */
class DebugCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('debug:swiftmailer')
            ->setAliases(array(
                'swiftmailer:debug',
            ))
            ->setDefinition(array(
                new InputArgument('name', InputArgument::OPTIONAL, 'A mailer name'),
            ))
            ->setDescription('Displays current mailers for an application')
            ->setHelp(<<<EOF
The <info>%command.name%</info> displays the configured mailers:

  <info>php %command.full_name% mailer-name</info>
EOF
            )
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        if ($name) {
            $this->outputMailer($output, $name);
        } else {
            $this->outputMailers($output);
        }
    }

    protected function outputMailers(OutputInterface $output, $routes = null)
    {
        $output->writeln($this->getHelper('formatter')->formatSection('swiftmailer', 'Current mailers'));

        $maxName = strlen('name');
        $maxTransport = strlen('transport');
        $maxSpool = strlen('spool');
        $maxDelivery = strlen('delivery');
        $maxSingleAddress = strlen('single address');

        $mailers = $this->getContainer()->getParameter('swiftmailer.mailers');
        foreach ($mailers as $name => $mailer) {
            $mailer = $this->getContainer()->get($mailer);
            $transport = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.transport.name', $name));
            $spool = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name)) ? 'YES' : 'NO';
            $delivery = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.delivery.enabled', $name)) ? 'YES' : 'NO';
            $singleAddress = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.single_address', $name));

            if ($this->isDefaultMailer($name)) {
                $name = sprintf('%s (default mailer)', $name);
            }
            $maxName = max($maxName, strlen($name));
            $maxTransport = max($maxTransport, strlen($transport));
            $maxSpool = max($maxSpool, strlen($spool));
            $maxDelivery = max($maxDelivery, strlen($delivery));
            $maxSingleAddress = max($maxSingleAddress, strlen($singleAddress));
        }
        $format  = '%-'.$maxName.'s %-'.$maxTransport.'s %-'.$maxSpool.'s %-'.$maxDelivery.'s %-'.$maxSingleAddress.'s';

        $format1  = '%-'.($maxName + 19).'s %-'.($maxTransport + 19).'s %-'.($maxSpool + 19).'s %-'.($maxDelivery + 19).'s %-'.($maxSingleAddress + 19).'s';
        $output->writeln(sprintf($format1, '<comment>Name</comment>', '<comment>Transport</comment>', '<comment>Spool</comment>', '<comment>Delivery</comment>', '<comment>Single Address</comment>'));
        foreach ($mailers as $name => $mailer) {
            $mailer = $this->getContainer()->get($mailer);
            $transport = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.transport.name', $name));
            $spool = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name)) ? 'YES' : 'NO';
            $delivery = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.delivery.enabled', $name)) ? 'YES' : 'NO';
            $singleAddress = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.single_address', $name));
            if ($this->isDefaultMailer($name)) {
                $name = sprintf('%s (default mailer)', $name);
            }
            $output->writeln(sprintf($format, $name, $transport, $spool, $delivery, $singleAddress));
        }
    }

    /**
     * @throws \InvalidArgumentException When route does not exist
     */
    protected function outputMailer(OutputInterface $output, $name)
    {
        try {
            $service = sprintf('swiftmailer.mailer.%s', $name);
            $mailer = $this->getContainer()->get($service);
        } catch (ServiceNotFoundException $e) {
            throw new \InvalidArgumentException(sprintf('The mailer "%s" does not exist.', $name));
        }

        $transport  = $mailer->getTransport();
        $spool = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name)) ? 'YES' : 'NO';
        $delivery = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.delivery.enabled', $name)) ? 'YES' : 'NO';
        $singleAddress = $this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.single_address', $name));

        $output->writeln($this->getHelper('formatter')->formatSection('swiftmailer', sprintf('Mailer "%s"', $name)));
        if ($this->isDefaultMailer($name)) {
            $output->writeln('This is the default mailer');
        }

        $output->writeln(sprintf('<comment>Name</comment>           %s', $name));
        $output->writeln(sprintf('<comment>Service</comment>        %s', $service));
        $output->writeln(sprintf('<comment>Class</comment>          %s', get_class($mailer)));
        $output->writeln(sprintf('<comment>Transport</comment>      %s (%s)', sprintf('swiftmailer.mailer.%s.transport.name', $name), get_class($transport)));
        $output->writeln(sprintf('<comment>Spool</comment>          %s', $spool));
        if ($this->getContainer()->hasParameter(sprintf('swiftmailer.spool.%s.file.path', $name))) {
            $output->writeln(sprintf('<comment>Spool file</comment>     %s', $this->getContainer()->getParameter(sprintf('swiftmailer.spool.%s.file.path', $name))));
        }
        $output->writeln(sprintf('<comment>Delivery</comment>       %s', $delivery));
        $output->writeln(sprintf('<comment>Single Address</comment> %s', $singleAddress));
    }

    private function isDefaultMailer($name)
    {
        return ($this->getContainer()->getParameter('swiftmailer.default_mailer') == $name || 'default' == $name) ? true : false;
    }
}
