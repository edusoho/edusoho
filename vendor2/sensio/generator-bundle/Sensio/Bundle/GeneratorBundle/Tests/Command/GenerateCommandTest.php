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

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\FormatterHelper;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\DependencyInjection\Container;

abstract class GenerateCommandTest extends \PHPUnit_Framework_TestCase
{
    protected function getHelperSet($input)
    {
        $dialog = new DialogHelper();
        $dialog->setInputStream($this->getInputStream($input));

        return new HelperSet(array(new FormatterHelper(), $dialog));
    }

    protected function getBundle()
    {
        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(sys_get_temp_dir()))
        ;

        return $bundle;
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input.str_repeat("\n", 10));
        rewind($stream);

        return $stream;
    }

    protected function getContainer()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel
            ->expects($this->any())
            ->method('getBundle')
            ->will($this->returnValue($this->getBundle()))
        ;
        $kernel
            ->expects($this->any())
            ->method('getBundles')
            ->will($this->returnValue(array($this->getBundle())))
        ;

        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem');
        $filesystem
            ->expects($this->any())
            ->method('isAbsolutePath')
            ->will($this->returnValue(true))
        ;

        $container = new Container();
        $container->set('kernel', $kernel);
        $container->set('filesystem', $filesystem);

        $container->setParameter('kernel.root_dir', sys_get_temp_dir());

        return $container;
    }
}
