<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Tests\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\CommandGenerator;

class CommandGeneratorTest extends GeneratorTest
{
    public function testGenerateController()
    {
        $commandName = 'app:foo-bar';
        $commandFile = 'Command/AppFooBarCommand.php';
        $commandPath = $this->tmpDir.'/'.$commandFile;

        $this->getGenerator()->generate($this->getBundle(), $commandName);

        $this->assertTrue(file_exists($commandPath), sprintf('%s file has been generated.', $commandFile));

        $commandContent = file_get_contents($commandPath);
        $strings = array(
            'namespace Foo\\BarBundle\\Command',
            'class AppFooBarCommand extends ContainerAwareCommand',
            sprintf("->setName('%s')", $commandName),
        );
        foreach ($strings as $string) {
            $this->assertContains($string, $commandContent);
        }
    }

    /**
     * @dataProvider getNames
     */
    public function testClassify($commandName, $className)
    {
        $generator = $this->getGenerator();
        $this->assertEquals($className, $generator->classify($commandName));
    }

    public function getNames()
    {
        return array(
            array('app', 'App'),
            array('app-foo', 'AppFoo'),
            array('app_foo', 'AppFoo'),
            array('app:foo-bar', 'AppFooBar'),
            array('app:foo:bar', 'AppFooBar'),
            array('app:foo:bar-baz', 'AppFooBarBaz'),
            array('app:foo:bar_baz', 'AppFooBarBaz'),
            array('app-foo:bar-baz:foo-bar', 'AppFooBarBazFooBar'),
        );
    }

    protected function getGenerator()
    {
        $generator = new CommandGenerator($this->filesystem);
        $generator->setSkeletonDirs(__DIR__.'/../../Resources/skeleton');

        return $generator;
    }

    protected function getBundle()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue($this->tmpDir));
        $bundle->expects($this->any())->method('getName')->will($this->returnValue('FooBarBundle'));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        return $bundle;
    }
}
