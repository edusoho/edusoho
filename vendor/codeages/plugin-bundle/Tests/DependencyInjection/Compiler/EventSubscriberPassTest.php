<?php

namespace Codeages\PluginBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Codeages\PluginBundle\DependencyInjection\Compiler\EventSubscriberPass;

class EventSubscriberPassTest extends TestCase
{
    public function testEventSubscriberWithoutInterface()
    {
        $services = array(
            'test_event_subscriber' => array(0 => array()),
        );
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')->getMock();

        $builder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->setMethods(array('findTaggedServiceIds', 'getDefinition'))->getMock();

        $builder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->onConsecutiveCalls(array(), $services));

        $builder->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $eventSubscriberPass = new EventSubscriberPass();
        $eventSubscriberPass->process($builder);
    }
}
