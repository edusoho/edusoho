<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Tests\DependencyInjection;

use Symfony\Bundle\AsseticBundle\DependencyInjection\AsseticExtension;
use Symfony\Bundle\AsseticBundle\DependencyInjection\Compiler\CheckClosureFilterPass;
use Symfony\Bundle\AsseticBundle\DependencyInjection\Compiler\CheckYuiFilterPass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;

class AsseticExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $kernel;
    private $container;

    public static function assertSaneContainer(Container $container, $message = '')
    {
        $errors = array();
        foreach ($container->getServiceIds() as $id) {
            try {
                $container->get($id);
            } catch (\Exception $e) {
                $errors[$id] = $e->getMessage();
            }
        }

        self::assertEquals(array(), $errors, $message);
    }

    protected function setUp()
    {
        if (!class_exists('Assetic\\AssetManager')) {
            $this->markTestSkipped('Assetic is not available.');
        }

        if (!class_exists('Twig_Environment')) {
            $this->markTestSkipped('Twig is not available.');
        }

        $this->kernel = $this->getMock('Symfony\\Component\\HttpKernel\\KernelInterface');

        $this->container = new ContainerBuilder();
        $this->container->addScope(new Scope('request'));
        $this->container->register('request', 'Symfony\\Component\\HttpFoundation\\Request')->setScope('request');
        $this->container->register('templating.helper.assets', $this->getMockClass('Symfony\\Component\\Templating\\Helper\\AssetsHelper'));
        $this->container->register('templating.helper.router', $this->getMockClass('Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RouterHelper'))
            ->addArgument(new Definition($this->getMockClass('Symfony\\Component\\Routing\\RouterInterface')));
        $this->container->register('twig', 'Twig_Environment');
        $this->container->setParameter('kernel.bundles', array());
        $this->container->setParameter('kernel.cache_dir', __DIR__);
        $this->container->setParameter('kernel.debug', false);
        $this->container->setParameter('kernel.root_dir', __DIR__);
        $this->container->setParameter('kernel.charset', 'UTF-8');
        $this->container->set('kernel', $this->kernel);
    }

    /**
     * @dataProvider getDebugModes
     */
    public function testDefaultConfig($debug)
    {
        $this->container->setParameter('kernel.debug', $debug);

        $extension = new AsseticExtension();
        $extension->load(array(array()), $this->container);

        $this->assertFalse($this->container->has('assetic.filter.yui_css'), '->load() does not load the yui_css filter when a yui value is not provided');
        $this->assertFalse($this->container->has('assetic.filter.yui_js'), '->load() does not load the yui_js filter when a yui value is not provided');

        $this->assertSaneContainer($this->getDumpedContainer());
    }

    public function getDebugModes()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider getFilterNames
     */
    public function testFilterConfigs($name, $config = array())
    {
        $extension = new AsseticExtension();
        $extension->load(array(array('filters' => array($name => $config))), $this->container);

        $this->assertSaneContainer($this->getDumpedContainer());
    }

    public function getFilterNames()
    {
        return array(
            array('autoprefixer'),
            array('closure', array('jar' => '/path/to/closure.jar')),
            array('coffee'),
            array('compass'),
            array('csscachebusting'),
            array('cssembed', array('jar' => '/path/to/cssembed.jar')),
            array('cssimport'),
            array('cssmin'),
            array('cssrewrite'),
            array('dart'),
            array('emberprecompile'),
            array('gss'),
            array('handlebars'),
            array('jpegoptim'),
            array('jpegtran'),
            array('jsmin'),
            array('jsminplus'),
            array('jsqueeze'),
            array('less'),
            array('lessphp'),
            array('minifycsscompressor'),
            array('optipng'),
            array('packager'),
            array('packer'),
            array('phpcssembed'),
            array('pngout'),
            array('roole'),
            array('sass'),
            array('scss'),
            array('scssphp', array('compass' => true)),
            array('sprockets', array('include_dirs' => array('foo'))),
            array('stylus'),
            array('typescript'),
            array('uglifycss'),
            array('uglifyjs'),
            array('uglifyjs2'),
            array('yui_css', array('jar' => '/path/to/yuicompressor.jar')),
            array('yui_js', array('jar' => '/path/to/yuicompressor.jar')),
        );
    }

    /**
     * @dataProvider getUseControllerKeys
     */
    public function testUseController($bool, $includes, $omits)
    {
        $extension = new AsseticExtension();
        $extension->load(array(array('use_controller' => $bool)), $this->container);

        foreach ($includes as $id) {
            $this->assertTrue($this->container->has($id), '"'.$id.'" is registered when use_controller is '.$bool);
        }

        foreach ($omits as $id) {
            $this->assertFalse($this->container->has($id), '"'.$id.'" is not registered when use_controller is '.$bool);
        }

        $this->assertSaneContainer($this->getDumpedContainer());
    }

    public function getUseControllerKeys()
    {
        return array(
            array(true, array('assetic.routing_loader', 'assetic.controller'), array()),
            array(false, array(), array('assetic.routing_loader', 'assetic.controller')),
        );
    }

    /**
     * @dataProvider getCacheBustingWorkerKeys
     */
    public function testCacheBustingWorker($enabled)
    {
        $extension = new AsseticExtension();
        $extension->load(array(array('workers' => array('cache_busting' => array('enabled' => $enabled)))), $this->container);

        $def = $this->container->getDefinition('assetic.worker.cache_busting');
        $this->assertSame($enabled, $def->hasTag('assetic.factory_worker'));

        $this->assertSaneContainer($this->getDumpedContainer());
    }

    public function getCacheBustingWorkerKeys()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider getClosureJarAndExpected
     */
    public function testClosureCompilerPass($jar, $expected)
    {
        $this->container->addCompilerPass(new CheckClosureFilterPass());

        $extension = new AsseticExtension();
        $extension->load(array(array(
            'filters' => array(
                'closure' => array('jar' => $jar),
            ),
        )), $this->container);

        $container = $this->getDumpedContainer();
        $this->assertSaneContainer($container);

        $this->assertTrue($this->container->getDefinition($expected)->hasTag('assetic.filter'));
        $this->assertNotEmpty($container->getParameter('assetic.filter.closure.java'));
    }

    public function getClosureJarAndExpected()
    {
        return array(
            array(null, 'assetic.filter.closure.api'),
            array('/path/to/closure.jar', 'assetic.filter.closure.jar'),
        );
    }

    public function testInvalidYuiConfig()
    {
        $this->setExpectedException('RuntimeException', 'assetic.filters.yui_js');

        $this->container->addCompilerPass(new CheckYuiFilterPass());

        $extension = new AsseticExtension();
        $extension->load(array(array(
            'filters' => array(
                'yui_js' => array(),
            ),
        )), $this->container);

        $this->getDumpedContainer();
    }

    private function getDumpedContainer()
    {
        static $i = 0;
        $class = 'AsseticExtensionTestContainer'.$i++;

        $this->container->compile();

        $dumper = new PhpDumper($this->container);
        eval('?>'.$dumper->dump(array('class' => $class)));

        $container = new $class();
        $container->enterScope('request');
        $container->set('request', Request::create('/'));
        $container->set('kernel', $this->kernel);

        return $container;
    }

    public function testCompassCanBeEnabled()
    {
        $extension = new AsseticExtension();
        $extension->load(array(array(
            'filters' => array(
                'scssphp' => array('compass' => true),
            ),
        )), $this->container);

        $this->assertTrue($this->container->get('assetic.filter.scssphp')->isCompassEnabled());
        //$this->getDumpedContainer();
    }
}
