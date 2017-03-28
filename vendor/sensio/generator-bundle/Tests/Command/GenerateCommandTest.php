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
use Symfony\Component\Filesystem\Filesystem;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\DependencyInjection\Container;

abstract class GenerateCommandTest extends \PHPUnit_Framework_TestCase
{
    protected $bundle;

    protected function tearDown()
    {
        if (null !== $this->bundle) {
            $fs = new Filesystem();
            $fs->remove($this->bundle->getPath());
        }
    }

    protected function getHelperSet()
    {
        return new HelperSet(array(new FormatterHelper(), new QuestionHelper()));
    }

    protected function setInputs($tester, $command, $input)
    {
        $input .= str_repeat("\n", 10);
        if (method_exists($tester, 'setInputs')) {
            $tester->setInputs(explode("\n", $input));
        } else {
            $stream = fopen('php://memory', 'r+', false);
            fwrite($stream, $input);
            rewind($stream);

            $command->getHelperSet()->get('question')->setInputStream($stream);
        }
    }

    protected function getBundle()
    {
        if (null !== $this->bundle) {
            return $this->bundle;
        }

        $tmpDir = sys_get_temp_dir().'/sf'.mt_rand(111111, 999999);
        @mkdir($tmpDir, 0777, true);

        $this->bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $this->bundle
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($tmpDir))
        ;

        return $this->bundle;
    }

    protected function getContainer()
    {
        $bundle = $this->getBundle();

        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
        $kernel
            ->expects($this->any())
            ->method('getBundle')
            ->will($this->returnValue($bundle))
        ;
        $kernel
            ->expects($this->any())
            ->method('getBundles')
            ->will($this->returnValue(array($bundle)))
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

        $container->setParameter('kernel.root_dir', $bundle->getPath());

        return $container;
    }
}
