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

    public function testCreateCrudWithAnnotationInNonAnnotationBundle()
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $routing = <<<DATA
acme_blog:
    resource: "@AcmeBlogBundle/Resources/config/routing.xml"
    prefix:   /
DATA;

        file_put_contents($rootDir.'/config/routing.yml', $routing);

        $options = array();
        $input = "AcmeBlogBundle:Blog/Post\ny\nannotation\n/foobar\n";
        $expected = array('Blog\\Post', 'annotation', 'foobar', true);

        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($this->getCommand($generator, $input));
        $tester->execute($options);

        $expected = 'acme_blog_post:';

        $this->assertContains($expected, file_get_contents($rootDir.'/config/routing.yml'));
    }

    public function testCreateCrudWithAnnotationInAnnotationBundle()
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $routing = <<<DATA
acme_blog:
    resource: "@AcmeBlogBundle/Controller/"
    type:     annotation
DATA;

        file_put_contents($rootDir.'/config/routing.yml', $routing);

        $options = array();
        $input = "AcmeBlogBundle:Blog/Post\ny\nyml\n/foobar\n";
        $expected = array('Blog\\Post', 'yml', 'foobar', true);

        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($this->getCommand($generator, $input));
        $tester->execute($options);

        $this->assertEquals($routing, file_get_contents($rootDir.'/config/routing.yml'));
    }

    public function testAddACrudWithOneAlreadyDefined()
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $routing = <<<DATA
acme_blog:
    resource: "@AcmeBlogBundle/Controller/OtherController.php"
    type:     annotation
DATA;

        file_put_contents($rootDir.'/config/routing.yml', $routing);

        $options = array();
        $input = "AcmeBlogBundle:Blog/Post\ny\nannotation\n/foobar\n";
        $expected = array('Blog\\Post', 'annotation', 'foobar', true);

        list($entity, $format, $prefix, $withWrite) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $entity, $this->getDoctrineMetadata(), $format, $prefix, $withWrite)
        ;

        $tester = new CommandTester($this->getCommand($generator, $input));
        $tester->execute($options);

        $expected = '@AcmeBlogBundle/Controller/PostController.php';

        $this->assertContains($expected, file_get_contents($rootDir.'/config/routing.yml'));
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

    protected function getBundle()
    {
        $bundle = parent::getBundle();

        $bundle
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('AcmeBlogBundle'))
        ;

        return $bundle;
    }

    protected function getContainer()
    {
        $container = parent::getContainer();

        $container->set('doctrine', $this->getDoctrine());

        return $container;
    }

    protected function getDoctrine()
    {
        $cache = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver')->getMock();
        $cache
            ->expects($this->any())
            ->method('getAllClassNames')
            ->will($this->returnValue(array('Acme\Bundle\BlogBundle\Entity\Post')))
        ;

        $configuration = $this->getMockBuilder('Doctrine\ORM\Configuration')->getMock();
        $configuration
            ->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($cache))
        ;

        $configuration
            ->expects($this->any())
            ->method('getEntityNamespaces')
            ->will($this->returnValue(array('AcmeBlogBundle' => 'Acme\Bundle\BlogBundle\Entity')))
        ;

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->getMock();
        $manager
            ->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration))
        ;

        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->getMock();
        $registry
            ->expects($this->any())
            ->method('getAliasNamespace')
            ->will($this->returnValue('Acme\Bundle\BlogBundle\Entity\Blog\Post'))
        ;

        $registry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($manager))
        ;

        return $registry;
    }
}
