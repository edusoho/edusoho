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
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\DependencyInjection\Container;

abstract class GenerateCommandTest extends \PHPUnit_Framework_TestCase
{
    protected function getHelperSet($input)
    {
        $question = new QuestionHelper();
        $question->setInputStream($this->getInputStream($input));

        return new HelperSet(array(new FormatterHelper(), $question));
    }

    protected function getBundle()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
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
        fwrite($stream, $input.str_repeat("\n", 10));
        rewind($stream);

        return $stream;
    }

    protected function getContainer()
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
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

        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
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
