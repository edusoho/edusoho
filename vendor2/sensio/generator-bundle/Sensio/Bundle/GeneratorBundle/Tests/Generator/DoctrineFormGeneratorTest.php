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

use Sensio\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator;

class DoctrineFormGeneratorTest extends GeneratorTest
{
    public function testGenerate()
    {
        $generator = new DoctrineFormGenerator($this->filesystem);
        $generator->setSkeletonDirs(__DIR__.'/../../Resources/skeleton');

        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue($this->tmpDir));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')->disableOriginalConstructor()->getMock();
        $metadata->identifier = array('id');
        $metadata->associationMappings = array('title' => array('type' => 'string'));

        $generator->generate($bundle, 'Post', $metadata);

        $this->assertTrue(file_exists($this->tmpDir.'/Form/PostType.php'));

        $content = file_get_contents($this->tmpDir.'/Form/PostType.php');
        $this->assertContains('->add(\'title\')', $content);
        $this->assertContains('class PostType extends AbstractType', $content);
        $this->assertContains("'data_class' => 'Foo\BarBundle\Entity\Post'", $content);
        $this->assertContains("'foo_barbundle_post'", $content);
    }
}
