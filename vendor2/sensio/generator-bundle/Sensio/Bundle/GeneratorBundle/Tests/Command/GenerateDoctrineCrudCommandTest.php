<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand;

class GenerateDoctrineCrudCommandTest extends GenerateCommandTest
{
    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand($options, $input, $expected)
    {
        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($this->getCommand($generator, $input));
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        return array(
            array(array(), "AcmeBlogBundle:Blog/Post\n", array('Blog\\Post', 'annotation', 'blog_post', false)),
            array(array('--entity' => 'AcmeBlogBundle:Blog/Post'), '', array('Blog\\Post', 'annotation', 'blog_post', false)),
            array(array(), "AcmeBlogBundle:Blog/Post\ny\nyml\nfoobar\n", array('Blog\\Post', 'yml', 'foobar', true)),
            array(array(), "AcmeBlogBundle:Blog/Post\ny\nyml\n/foobar\n", array('Blog\\Post', 'yml', 'foobar', true)),
            array(array('--entity' => 'AcmeBlogBundle:Blog/Post', '--format' => 'yml', '--route-prefix' => 'foo', '--with-write' => true), '', array('Blog\\Post', 'yml', 'foo', true)),
            array(array('entity' => 'AcmeBlogBundle:Blog/Post'), "\ny\nyml\nfoobar\n", array('Blog\\Post', 'yml', 'foobar', true)),
        );
    }

    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand($options, $expected)
    {
        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($this->getCommand($generator, ''));
        $tester->execute($options, array('interactive' => false));
    }

    public function getNonInteractiveCommandData()
    {
        return array(
            array(array('--entity' => 'AcmeBlogBundle:Blog/Post'), array('Blog\\Post', 'annotation', 'blog_post', false)),
            array(array('--entity' => 'AcmeBlogBundle:Blog/Post', '--format' => 'yml', '--route-prefix' => 'foo', '--with-write' => true), array('Blog\\Post', 'yml', 'foo', true)),
        );
    }

    protected function getCommand($generator, $input)
    {
        $command = $this
            ->getMockBuilder('Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand')
            ->setMethods(array('getEntityMetadata'))
            ->getMock()
        ;

        $command
            ->expects($this->any())
            ->method('getEntityMetadata')
            ->will($this->returnValue(array($this->getDoctrineMetadata())))
        ;

        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet($input));
        $command->setGenerator($generator);
        $command->setFormGenerator($this->getFormGenerator());

        return $command;
    }

    protected function getDoctrineMetadata()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    protected function getGenerator()
    {
        // get a noop generator
        return $this
            ->getMockBuilder('Sensio\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock()
        ;
    }

    protected function getFormGenerator()
    {
        return $this
            ->getMockBuilder('Sensio\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock()
        ;
    }

    protected function getContainer()
    {
        $container = parent::getContainer();

        $registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $registry
            ->expects($this->any())
            ->method('getAliasNamespace')
            ->will($this->returnValue('Foo\\FooBundle\\Entity'))
        ;

        $container->set('doctrine', $registry);

        return $container;
    }
}
