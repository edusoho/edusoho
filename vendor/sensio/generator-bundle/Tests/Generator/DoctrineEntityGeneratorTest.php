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

use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;

class DoctrineEntityGeneratorTest extends GeneratorTest
{
    const FORMAT_XML = 'xml';
    const FORMAT_YAML = 'yml';
    const FORMAT_ANNOTATION = 'annotation';

    const WITH_REPOSITORY = true;
    const WITHOUT_REPOSITORY = false;

    public function testGenerateYaml()
    {
        $this->generate(self::FORMAT_YAML);

        $files = array(
            'Entity/Foo.php',
            'Resources/config/doctrine/Foo.orm.yml',
        );

        $this->assertFilesExists($files);
        $this->assertAttributesAndMethodsExists();
    }

    public function testGenerateSubNamespacedYaml()
    {
        $this->generateSubNamespaced(self::FORMAT_YAML);

        $files = array(
            'Entity/Sub/Foo.php',
            'Resources/config/doctrine/Sub.Foo.orm.yml',
        );

        $this->assertFilesExists($files);
        $this->assertAttributesAndMethodsExists(array(), 'Sub/Foo');
    }

    public function testGenerateXml()
    {
        $this->generate(self::FORMAT_XML);

        $files = array(
            'Entity/Foo.php',
            'Resources/config/doctrine/Foo.orm.xml',
        );

        $this->assertFilesExists($files);
        $this->assertAttributesAndMethodsExists();
    }

    public function testGenerateSubNamespacedXml()
    {
        $this->generateSubNamespaced(self::FORMAT_XML);

        $files = array(
            'Entity/Sub/Foo.php',
            'Resources/config/doctrine/Sub.Foo.orm.xml',
        );

        $this->assertFilesExists($files);
        $this->assertAttributesAndMethodsExists(array(), 'Sub/Foo');
    }

    public function testGenerateAnnotation()
    {
        $this->generate(self::FORMAT_ANNOTATION);

        $files = array(
            'Entity/Foo.php',
        );

        $annotations = array(
            '@ORM\Table(name="foo")',
            '@ORM\Column(name="bar"',
            '@ORM\Column(name="baz"',
        );

        $this->assertFilesExists($files);
        $this->assertAttributesAndMethodsExists($annotations);
    }

    public function testGenerateSubNamespacedAnnotation()
    {
        $this->generateSubNamespaced(self::FORMAT_ANNOTATION);

        $files = array(
            'Entity/Sub/Foo.php',
        );

        $annotations = array(
            '@ORM\Table(name="sub_foo")',
            '@ORM\Column(name="bar"',
            '@ORM\Column(name="baz"',
        );

        $this->assertFilesExists($files);
        $this->assertAttributesAndMethodsExists($annotations, 'Sub/Foo');
    }

    protected function assertFilesExists(array $files)
    {
        foreach ($files as $file) {
            $this->assertTrue(file_exists($this->tmpDir.'/'.$file), sprintf('%s has been generated', $file));
        }
    }

    protected function assertAttributesAndMethodsExists(array $otherStrings = array(), $entity = 'Foo')
    {
        $content = file_get_contents($this->tmpDir.'/Entity/'.$entity.'.php');

        $strings = array(
            'namespace Foo\\BarBundle\\Entity',
            'class Foo',
            'private $id',
            'private $bar',
            'private $baz',
            'public function getId',
            'public function getBar',
            'public function getBaz',
            'public function setBar',
            'public function setBaz',
        );

        $strings = array_merge($strings, $otherStrings);

        foreach ($strings as $string) {
            $this->assertContains($string, $content);
        }
    }

    protected function generate($format)
    {
        $this->getGenerator()->generate($this->getBundle(), 'Foo', $format, $this->getFields());
    }

    protected function generateSubNamespaced($format)
    {
        $this->getGenerator()->generate($this->getBundle(), 'Sub\Foo', $format, $this->getFields());
    }

    protected function getGenerator()
    {
        $generator = new DoctrineEntityGenerator($this->filesystem, $this->getRegistry());
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

    protected function getFields()
    {
        return array(
            array('fieldName' => 'bar', 'type' => 'string', 'length' => 255),
            array('fieldName' => 'baz', 'type' => 'integer', 'length' => 11),
        );
    }

    public function getRegistry()
    {
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->getMock();
        $registry->expects($this->any())->method('getManager')->will($this->returnValue($this->getManager()));
        $registry->expects($this->any())->method('getAliasNamespace')->will($this->returnValue('Foo\\BarBundle\\Entity'));

        return $registry;
    }

    public function getManager()
    {
        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->getMock();
        $manager->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($this->getConfiguration()));

        return $manager;
    }

    public function getConfiguration()
    {
        $config = $this->getMockBuilder('Doctrine\ORM\Configuration')->getMock();
        $config->expects($this->any())->method('getEntityNamespaces')->will($this->returnValue(array('Foo\\BarBundle')));

        return $config;
    }
}
