<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Tests\Manipulator;

use Sensio\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;

class RoutingManipulatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getImportedResourceYamlKeys
     */
    public function testGetImportedResourceYamlKey($bundleName, $prefix, $expectedKey)
    {
        $manipulator = new RoutingManipulator(__FILE__);
        $key = $manipulator->getImportedResourceYamlKey($bundleName, $prefix);

        $this->assertEquals($expectedKey, $key);
    }

    public function getImportedResourceYamlKeys()
    {
        return array(
            array('AppBundle', '', 'app'),
            array('AppBundle', '/', 'app'),
            array('AppBundle', '//', 'app'),
            array('AppBundle', '/{foo}', 'app'),
            array('AppBundle', '/{_foo}', 'app'),
            array('AppBundle', '/{/foo}', 'app'),
            array('AppBundle', '/{/foo/}', 'app'),
            array('AppBundle', '/{_locale}', 'app'),
            array('AppBundle', '/{_locale}/foo', 'app_foo'),
            array('AppBundle', '/{_locale}/foo/', 'app_foo'),
            array('AppBundle', '/{_locale}/foo/{_format}', 'app_foo'),
            array('AppBundle', '/{_locale}/foo/{_format}/', 'app_foo'),
            array('AppBundle', '/{_locale}/foo/{_format}/bar', 'app_foo_bar'),
            array('AppBundle', '/{_locale}/foo/{_format}/bar/', 'app_foo_bar'),
            array('AppBundle', '/{_locale}/foo/{_format}/bar//', 'app_foo_bar'),
            array('AcmeBlogBundle', '', 'acme_blog'),
            array('AcmeBlogBundle', '/', 'acme_blog'),
            array('AcmeBlogBundle', '//', 'acme_blog'),
            array('AcmeBlogBundle', '/{_locale}', 'acme_blog'),
            array('AcmeBlogBundle', '/{_locale}/foo', 'acme_blog_foo'),
            array('AcmeBlogBundle', '/{_locale}/foo/', 'acme_blog_foo'),
            array('AcmeBlogBundle', '/{_locale}/foo/{_format}', 'acme_blog_foo'),
            array('AcmeBlogBundle', '/{_locale}/foo/{_format}/', 'acme_blog_foo'),
            array('AcmeBlogBundle', '/{_locale}/foo/{_format}/bar', 'acme_blog_foo_bar'),
            array('AcmeBlogBundle', '/{_locale}/foo/{_format}/bar/', 'acme_blog_foo_bar'),
            array('AcmeBlogBundle', '/{_locale}/foo/{_format}/bar//', 'acme_blog_foo_bar'),
        );
    }

    public function testHasResourceInAnnotation()
    {
        $tmpDir = sys_get_temp_dir().'/sf';
        @mkdir($tmpDir, 0777, true);
        $file = tempnam($tmpDir, 'routing');

        $routing = <<<DATA
acme_demo:
    resource: "@AcmeDemoBundle/Controller/"
    type:     annotation
DATA;

        file_put_contents($file, $routing);

        $manipulator = new RoutingManipulator($file);
        $this->assertTrue($manipulator->hasResourceInAnnotation('AcmeDemoBundle'));
    }

    public function testHasResourceInAnnotationReturnFalseIfOnlyOneControllerDefined()
    {
        $tmpDir = sys_get_temp_dir().'/sf';
        @mkdir($tmpDir, 0777, true);
        $file = tempnam($tmpDir, 'routing');

        $routing = <<<DATA
acme_demo_post:
    resource: "@AcmeDemoBundle/Controller/PostController.php"
    type:     annotation
DATA;

        file_put_contents($file, $routing);

        $manipulator = new RoutingManipulator($file);
        $this->assertFalse($manipulator->hasResourceInAnnotation('AcmeDemoBundle'));
    }
}
