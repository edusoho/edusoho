<?php

namespace Symfony\Bundle\SwiftmailerBundle\Tests\Command;

use Symfony\Bundle\SwiftmailerBundle\Command\SendEmailCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SendEmailCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testRecoverSpoolTransport()
    {
        $realTransport = $this->getMockBuilder('Swift_Transport')->getMock();

        $spool = $this->getMockBuilder('Swift_Spool')->getMock();
        $spool
            ->expects($this->once())
            ->method('flushQueue')
            ->with($realTransport)
            ->will($this->returnValue(5))
        ;

        $spoolTransport = new \Swift_Transport_SpoolTransport(new \Swift_Events_SimpleEventDispatcher(), $spool);

        $container = $this->buildContainer($spoolTransport, $realTransport);
        $tester = $this->executeCommand($container);

        $this->assertStringEndsWith("5 emails sent\n", $tester->getDisplay());
    }

    public function testRecoverLoadbalancedTransportWithSpool()
    {
        $realTransport = $this->getMockBuilder('Swift_Transport')->getMock();

        $spool = $this->getMockBuilder('Swift_Spool')->getMock();
        $spool
            ->expects($this->once())
            ->method('flushQueue')
            ->with($realTransport)
            ->will($this->returnValue(7))
        ;

        $spoolTransport = new \Swift_Transport_SpoolTransport(new \Swift_Events_SimpleEventDispatcher(), $spool);

        $loadBalancedTransport = new \Swift_Transport_LoadBalancedTransport();
        $loadBalancedTransport->setTransports(array($spoolTransport));

        $container = $this->buildContainer($loadBalancedTransport, $realTransport);
        $tester = $this->executeCommand($container);

        $this->assertStringEndsWith("7 emails sent\n", $tester->getDisplay());
    }

    /**
     * @return Container
     */
    private function buildContainer(\Swift_Transport $transport, \Swift_Transport $realTransport, $name = 'default')
    {
        $mailer = new \Swift_Mailer($transport);

        $container = new Container();
        $container->set(sprintf('swiftmailer.mailer.%s', $name), $mailer);
        $container->set(sprintf('swiftmailer.mailer.%s.transport.real', $name), $realTransport);
        $container->setParameter('swiftmailer.mailers', array($name => $mailer));
        $container->setParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name), true);

        return $container;
    }

    /**
     * @return CommandTester
     */
    private function executeCommand(ContainerInterface $container, $input = array(), $options = array())
    {
        $command = new SendEmailCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute($input, $options);

        return $tester;
    }
}
