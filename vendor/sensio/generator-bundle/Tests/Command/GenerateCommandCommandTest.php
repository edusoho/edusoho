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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Sensio\Bundle\GeneratorBundle\Command\GenerateCommandCommand;

class GenerateCommandCommandTest extends GenerateCommandTest
{
    protected $generator;
    protected $bundle;
    protected $tmpDir;

    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand($options, $input, $expected)
    {
        list($bundle, $name) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $name)
        ;

        $tester = $this->getCommandTester($generator, $input);
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        return array(
            array(
                array(),
                "FooBarBundle\napp:foo-bar\n",
                array('FooBarBundle', 'app:foo-bar'),
            ),

            array(
                array('bundle' => 'FooBarBundle'),
                "app:foo-bar\n",
                array('FooBarBundle', 'app:foo-bar'),
            ),

            array(
                array('name' => 'app:foo-bar'),
                "FooBarBundle\n",
                array('FooBarBundle', 'app:foo-bar'),
            ),

            array(
                array('bundle' => 'FooBarBundle', 'name' => 'app:foo-bar'),
                '',
                array('FooBarBundle', 'app:foo-bar'),
            ),
        );
    }

    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand($options, $expected)
    {
        list($bundle, $name) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $name)
        ;

        $tester = $this->getCommandTester($generator);
        $tester->execute($options, array('interactive' => false));
    }

    public function getNonInteractiveCommandData()
    {
        $tmp = sys_get_temp_dir();

        return array(
            array(
                array('bundle' => 'FooBarBundle', 'name' => 'app:my-command'),
                array('FooBarBundle', 'app:my-command'),
            ),
        );
    }

    protected function getCommand($generator, $input)
    {
        $command = new GenerateCommandCommand();

        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet($input));
        $command->setGenerator($generator);

        return $command;
    }

    protected function getCommandTester($generator, $input = '')
    {
        return new CommandTester($this->getCommand($generator, $input));
    }

    protected function getApplication($input = '')
    {
        $application = new Application();

        $command = new GenerateCommandCommand();
        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet($input));
        $command->setGenerator($this->getGenerator());

        $application->add($command);

        return $application;
    }

    protected function getGenerator()
    {
        if (null === $this->generator) {
            $this->setGenerator();
        }

        return $this->generator;
    }

    protected function setGenerator()
    {
        // get a noop generator
        $this->generator = $this
            ->getMockBuilder('Sensio\Bundle\GeneratorBundle\Generator\CommandGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock()
        ;
    }

    protected function getBundle()
    {
        if (null == $this->bundle) {
            $this->setBundle();
        }

        return $this->bundle;
    }

    protected function setBundle()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue($this->tmpDir));
        $bundle->expects($this->any())->method('getName')->will($this->returnValue('FooBarBundle'));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        $this->bundle = $bundle;
    }
}
