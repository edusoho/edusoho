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
use Sensio\Bundle\GeneratorBundle\Command\GenerateControllerCommand;

class GenerateControllerCommandTest extends GenerateCommandTest
{
    protected $generator;

    /**
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand($options, $input, $expected)
    {
        list($controller, $routeFormat, $templateFormat, $actions) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $controller, $routeFormat, $templateFormat, $actions)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $this->setInputs($tester, $command, $input);
        $tester->execute($options);
    }

    public function getInteractiveCommandData()
    {
        return array(
            array(array(), "AcmeBlogBundle:Post\n", array('Post', 'annotation', 'twig', array())),
            array(array('--controller' => 'AcmeBlogBundle:Post'), '', array('Post', 'annotation', 'twig', array())),

            array(array(), "AcmeBlogBundle:Post\nyml\nphp\n", array('Post', 'yml', 'php', array())),

            array(array(), "AcmeBlogBundle:Post\nyml\nphp\nshowAction\n\n\ngetListAction\n/_getlist/{max}\nAcmeBlogBundle:Lists:post.html.php\n", array('Post', 'yml', 'php', array(
                'showAction' => array(
                    'name' => 'showAction',
                    'route' => '/show',
                    'placeholders' => array(),
                    'template' => 'AcmeBlogBundle:Post:show.html.php',
                ),
                'getListAction' => array(
                    'name' => 'getListAction',
                    'route' => '/_getlist/{max}',
                    'placeholders' => array('max'),
                    'template' => 'AcmeBlogBundle:Lists:post.html.php',
                ),
            ))),

            array(array('--route-format' => 'xml', '--template-format' => 'php', '--actions' => array('showAction:/{slug}:AcmeBlogBundle:article.html.php')), 'AcmeBlogBundle:Post', array('Post', 'xml', 'php', array(
                'showAction' => array(
                    'name' => 'showAction',
                    'route' => '/{slug}',
                    'placeholders' => array('slug'),
                    'template' => 'AcmeBlogBundle:article.html.php',
                ),
            ))),
        );
    }

    /**
     * @dataProvider getNonInteractiveCommandData
     */
    public function testNonInteractiveCommand($options, $expected)
    {
        list($controller, $routeFormat, $templateFormat, $actions) = $expected;

        $generator = $this->getGenerator();
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->getBundle(), $controller, $routeFormat, $templateFormat, $actions)
        ;

        $tester = new CommandTester($command = $this->getCommand($generator));
        $tester->execute($options, array('interactive' => false));
    }

    public function getNonInteractiveCommandData()
    {
        return array(
            array(array('--controller' => 'AcmeBlogBundle:Post'), array('Post', 'annotation', 'twig', array())),
            array(array('--controller' => 'AcmeBlogBundle:Post', '--route-format' => 'yml', '--template-format' => 'php'), array('Post', 'yml', 'php', array())),
            array(array('--controller' => 'AcmeBlogBundle:Post', '--actions' => array('showAction getListAction:/_getlist/{max}:AcmeBlogBundle:List:post.html.twig createAction:/admin/create')), array('Post', 'annotation', 'twig', array(
                'showAction' => array(
                    'name' => 'showAction',
                    'route' => '/show',
                    'placeholders' => array(),
                    'template' => 'default',
                ),
                'getListAction' => array(
                    'name' => 'getListAction',
                    'route' => '/_getlist/{max}',
                    'placeholders' => array('max'),
                    'template' => 'AcmeBlogBundle:List:post.html.twig',
                ),
                'createAction' => array(
                    'name' => 'createAction',
                    'route' => '/admin/create',
                    'placeholders' => array(),
                    'template' => 'default',
                ),
            ))),
            array(array('--controller' => 'AcmeBlogBundle:Post', '--route-format' => 'xml', '--template-format' => 'php', '--actions' => array('showAction::')), array('Post', 'xml', 'php', array(
                'showAction' => array(
                    'name' => 'showAction',
                    'route' => '/show',
                    'placeholders' => array(),
                    'template' => 'default',
                ),
            ))),
        );
    }

    protected function getCommand($generator)
    {
        $command = $this
            ->getMockBuilder('Sensio\Bundle\GeneratorBundle\Command\GenerateControllerCommand')
            ->setMethods(array('generateRouting'))
            ->getMock()
        ;

        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet());
        $command->setGenerator($generator);

        return $command;
    }

    protected function getApplication($input = '')
    {
        $application = new Application();

        $command = new GenerateControllerCommand();
        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet($input));
        $command->setGenerator($this->getGenerator());

        $application->add($command);

        return $application;
    }

    protected function getGenerator()
    {
        if (null == $this->generator) {
            $this->setGenerator();
        }

        return $this->generator;
    }

    protected function setGenerator()
    {
        // get a noop generator
        $this->generator = $this
            ->getMockBuilder('Sensio\Bundle\GeneratorBundle\Generator\ControllerGenerator')
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
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue(''));
        $bundle->expects($this->any())->method('getName')->will($this->returnValue('FooBarBundle'));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        $this->bundle = $bundle;
    }
}
