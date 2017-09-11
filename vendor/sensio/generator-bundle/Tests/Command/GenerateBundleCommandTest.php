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

use Sensio\Bundle\GeneratorBundle\Model\Bundle;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateBundleCommandTest extends GenerateCommandTest
{
    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand($options, $input, $expected)
    {
        list($namespace, $bundleName, $dir, $format, $shared) = $expected;
        $bundle = new Bundle($namespace, $bundleName, $dir, $format, $shared);

        $container = $this->getContainer();

        // not shared? the tests should be at the root of the project
        if (!$shared) {
            $bundle->setTestsDirectory($container->getParameter('kernel.root_dir').'/../tests/'.$bundleName);
        }

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generateBundle')
            ->with($bundle)
        ;

        $tester = new CommandTester($this->getCommand($generator, $input, $container));
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        $tmp = sys_get_temp_dir();

        return array(
            array(
                array('--shared' => true, '--dir' => $tmp, '--format' => 'annotation'),
                // shared, namespace, bundle name, directory, format
                "\nFoo/BarBundle\n\n\n\n",
                array('Foo\BarBundle', 'FooBarBundle', $tmp.'/', 'annotation', true),
            ),
            array(
                array(),
                // shared, namespace, bundle name, directory, format
                "y\nFoo/BarBundle\nBarBundle\nfoo\nyml",
                array('Foo\BarBundle', 'BarBundle', 'foo/', 'yml', true),
            ),
            array(
                array('--shared' => true, '--dir' => $tmp, '--format' => 'yml', '--bundle-name' => 'BarBundle'),
                // shared, namespace, bundle name, directory, format
                "\nFoo/BarBundle\n\n\n\n",
                array('Foo\BarBundle', 'BarBundle', $tmp.'/', 'yml', true),
            ),
            array(
                array(),
                // shared, namespace, bundle name, directory, format
                "n\nBazBundle\n\nsrc\nannotation",
                array('BazBundle', 'BazBundle', 'src/', 'annotation', false),
            ),
        );
    }

    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand($options, $expected)
    {
        list($namespace, $bundleName, $dir, $format, $shared) = $expected;
        $bundle = new Bundle($namespace, $bundleName, $dir, $format, $shared);

        $container = $this->getContainer();

        // not shared? the tests should be at the root of the project
        if (!$shared) {
            $bundle->setTestsDirectory($container->getParameter('kernel.root_dir').'/../tests/'.$bundleName);
        }

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generateBundle')
            ->with($bundle)
        ;

        $tester = new CommandTester($this->getCommand($generator, '', $container));
        $tester->execute($options, array('interactive' => false));
    }

    public function getNonInteractiveCommandData()
    {
        $tmp = sys_get_temp_dir();

        return array(
            array(
                array('--shared' => true, '--dir' => $tmp, '--namespace' => 'Foo/BarBundle'),
                array('Foo\BarBundle', 'FooBarBundle', $tmp.'/', 'annotation', true),
            ),
            array(
                array('--shared' => true, '--dir' => $tmp, '--namespace' => 'Foo/BarBundle', '--format' => 'yml', '--bundle-name' => 'BarBundle'),
                array('Foo\BarBundle', 'BarBundle', $tmp.'/', 'yml', true),
            ),
            array(
                array('--dir' => $tmp, '--namespace' => 'BazBundle', '--format' => 'yml', '--bundle-name' => 'BazBundle'),
                array('BazBundle', 'BazBundle', $tmp.'/', 'yml', false),
            ),
        );
    }

    protected function getCommand($generator, $input, $container)
    {
        $command = $this
            ->getMockBuilder('Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand')
            ->setMethods(array('checkAutoloader', 'updateKernel', 'updateRouting'))
            ->getMock()
        ;

        $command->setContainer($container);
        $command->setHelperSet($this->getHelperSet($input));
        $command->setGenerator($generator);

        return $command;
    }

    protected function getGenerator()
    {
        // get a noop generator
        return $this
            ->getMockBuilder('Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generateBundle'))
            ->getMock()
        ;
    }
}
