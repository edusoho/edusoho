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
        $this->generateForm(false);

        $this->assertTrue(file_exists($this->tmpDir.'/Form/PostType.php'));

        $content = file_get_contents($this->tmpDir.'/Form/PostType.php');
        $this->assertContains('namespace Foo\BarBundle\Form', $content);
        $this->assertContains('class PostType extends AbstractType', $content);
        $this->assertContains('->add(\'title\')', $content);
        $this->assertContains('->add(\'createdAt\')', $content);
        $this->assertContains('->add(\'publishedAt\')', $content);
        $this->assertContains('->add(\'updatedAt\')', $content);
        $this->assertContains('public function configureOptions(OptionsResolver $resolver)', $content);
        $this->assertContains('\'data_class\' => \'Foo\BarBundle\Entity\Post\'', $content);
    }

    public function testGenerateSubNamespacedEntity()
    {
        $this->generateSubNamespacedEntityForm(false);

        $this->assertTrue(file_exists($this->tmpDir.'/Form/Blog/PostType.php'));

        $content = file_get_contents($this->tmpDir.'/Form/Blog/PostType.php');
        $this->assertContains('namespace Foo\BarBundle\Form\Blog', $content);
        $this->assertContains('class PostType extends AbstractType', $content);
        $this->assertContains('->add(\'title\')', $content);
        $this->assertContains('->add(\'createdAt\')', $content);
        $this->assertContains('->add(\'publishedAt\')', $content);
        $this->assertContains('->add(\'updatedAt\')', $content);
        $this->assertContains('public function configureOptions(OptionsResolver $resolver)', $content);
        $this->assertContains('\'data_class\' => \'Foo\BarBundle\Entity\Blog\Post\'', $content);
        $this->assertContains('public function getBlockPrefix()', $content);
        $this->assertContains('return \'foo_barbundle_blog_post\';', $content);
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            // Symfony >= 2.8
            $this->assertNotContains('public function getName()', $content);
        } else {
            // BC Symfony 2.7
            $this->assertContains('public function getName()', $content);
        }
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp: Unable to generate the PostType form class as it already exists under the .* file
     */
    public function testNonOverwrittenForm()
    {
        $this->generateForm(false);
        $this->generateForm(false);
    }

    public function testOverwrittenForm()
    {
        $this->generateForm(false);
        $this->generateForm(true);

        $this->assertTrue(file_exists($this->tmpDir.'/Form/PostType.php'));
    }

    private function generateForm($overwrite)
    {
        $generator = new DoctrineFormGenerator($this->filesystem);
        $generator->setSkeletonDirs(__DIR__.'/../../Resources/skeleton');

        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue($this->tmpDir));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')->disableOriginalConstructor()->getMock();
        $metadata->identifier = array('id');
        $metadata->fieldMappings = array(
            'title' => array('type' => 'string'),
            'createdAt' => array('type' => 'date'),
            'publishedAt' => array('type' => 'time'),
            'updatedAt' => array('type' => 'datetime'),
        );
        $metadata->associationMappings = $metadata->fieldMappings;

        $generator->generate($bundle, 'Post', $metadata, $overwrite);
    }

    private function generateSubNamespacedEntityForm($overwrite)
    {
        $generator = new DoctrineFormGenerator($this->filesystem);
        $generator->setSkeletonDirs(__DIR__.'/../../Resources/skeleton');

        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue($this->tmpDir));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')->disableOriginalConstructor()->getMock();
        $metadata->identifier = array('id');
        $metadata->fieldMappings = array(
            'title' => array('type' => 'string'),
            'createdAt' => array('type' => 'date'),
            'publishedAt' => array('type' => 'time'),
            'updatedAt' => array('type' => 'datetime'),
        );
        $metadata->associationMappings = $metadata->fieldMappings;

        $generator->generate($bundle, 'Blog\Post', $metadata, $overwrite);
    }
}
