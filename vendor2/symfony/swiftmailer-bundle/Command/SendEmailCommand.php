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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Send Emails from the spool.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Clément JOBEILI <clement.jobeili@gmail.com>
 */
class SendEmailCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('swiftmailer:spool:send')
            ->setDescription('Sends emails from the spool')
            ->addOption('message-limit', 0, InputOption::VALUE_OPTIONAL, 'The maximum number of messages to send.')
            ->addOption('time-limit', 0, InputOption::VALUE_OPTIONAL, 'The time limit for sending messages (in seconds).')
            ->addOption('recover-timeout', 0, InputOption::VALUE_OPTIONAL, 'The timeout for recovering messages that have taken too long to send (in seconds).')
            ->addOption('mailer', null, InputOption::VALUE_OPTIONAL, 'The mailer name.')
            ->setHelp(<<<EOF
The <info>swiftmailer:spool:send</info> command sends all emails from the spool.

<info>php app/console swiftmailer:spool:send --message-limit=10 --time-limit=10 --recover-timeout=900 --mailer=default</info>

EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption('mailer');
        if ($name) {
            $this->processMailer($name, $input, $output);
        } else {
            $mailers = array_keys($this->getContainer()->getParameter('swiftmailer.mailers'));
            foreach ($mailers as $name) {
                $this->processMailer($name, $input, $output);
            }
        }
    }

    private function processMailer($name, $input, $output)
    {
        if (!$this->getContainer()->has(sprintf('swiftmailer.mailer.%s', $name))) {
            throw new \InvalidArgumentException(sprintf('The mailer "%s" does not exist.', $name));
        }

        $output->write(sprintf('<info>[%s]</info> Processing <info>%s</info> mailer... ', date('Y-m-d H:i:s'), $name));
        if ($this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name))) {
            $mailer = $this->getContainer()->get(sprintf('swiftmailer.mailer.%s', $name));
            $transport = $mailer->getTransport();
            if ($transport instanceof \Swift_Transport_SpoolTransport) {
                $spool = $transport->getSpool();
                if ($spool instanceof \Swift_ConfigurableSpool) {
                    $spool->setMessageLimit($input->getOption('message-limit'));
                    $spool->setTimeLimit($input->getOption('time-limit'));
                }
                if ($spool instanceof \Swift_FileSpool) {
                    if (null !== $input->getOption('recover-timeout')) {
                        $spool->recover($input->getOption('recover-timeout'));
                    } else {
                        $spool->recover();
                    }
                }
                $sent = $spool->flushQueue($this->getContainer()->get(sprintf('swiftmailer.mailer.%s.transport.real', $name)));

                $output->writeln(sprintf('<comment>%d</comment> emails sent', $sent));
            }
        } else {
            $output->writeln('No email to send as the spool is disabled.');
        }
    }
}
