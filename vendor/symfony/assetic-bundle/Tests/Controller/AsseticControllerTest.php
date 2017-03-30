<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Tests\Controller;

use Symfony\Bundle\AsseticBundle\Controller\AsseticController;

class AsseticControllerTest extends \PHPUnit_Framework_TestCase
{
    private $request;
    private $headers;
    private $am;
    private $cache;
    private $controller;

    protected function setUp()
    {
        if (!class_exists('Assetic\\AssetManager')) {
            $this->markTestSkipped('Assetic is not available.');
        }

        $this->request = $this->getMockBuilder('Symfony\\Component\\HttpFoundation\\Request')->setMethods(array('getETags', 'getMethod'))->getMock();
        $this->headers = $this->getMockBuilder('Symfony\\Component\\HttpFoundation\\ParameterBag')->getMock();
        $this->request->headers = $this->headers;
        $this->am = $this->getMockBuilder('Assetic\\Factory\\LazyAssetManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cache = $this->getMockBuilder('Assetic\\Cache\\CacheInterface')->getMock();

        $this->request->expects($this->any())
            ->method('getMethod')
            ->willReturn('GET');

        $this->controller = new AsseticController($this->am, $this->cache);
    }

    public function testRenderNotFound()
    {
        $this->setExpectedException('Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException');

        $name = 'foo';

        $this->am->expects($this->once())
            ->method('has')
            ->with($name)
            ->will($this->returnValue(false));

        $this->controller->render($this->request, $name);
    }

    public function testRenderLastModifiedFresh()
    {
        $asset = $this->getMockBuilder('Assetic\\Asset\\AssetInterface')->getMock();

        $name = 'foo';
        $lastModified = strtotime('2010-10-10 10:10:10');
        $ifModifiedSince = gmdate('D, d M Y H:i:s', $lastModified).' GMT';

        $asset->expects($this->any())
            ->method('getFilters')
            ->will($this->returnValue(array()));
        $this->am->expects($this->any())
            ->method('has')
            ->with($name)
            ->will($this->returnValue(true));
        $this->am->expects($this->any())
            ->method('get')
            ->with($name)
            ->will($this->returnValue($asset));
        $this->am->expects($this->any())
            ->method('getLastModified')
            ->with($asset)
            ->will($this->returnValue($lastModified));
        $this->headers->expects($this->any())
            ->method('get')
            ->with('If-Modified-Since')
            ->will($this->returnValue($ifModifiedSince));
        $asset->expects($this->never())
            ->method('dump');

        $response = $this->controller->render($this->request, $name);

        $this->assertEquals(304, $response->getStatusCode(), '->render() sends a Not Modified response when If-Modified-Since is fresh');
    }

    public function testRenderLastModifiedStale()
    {
        $asset = $this->getMockBuilder('Assetic\\Asset\\AssetInterface')->getMock();

        $name = 'foo';
        $content = '==ASSET_CONTENT==';
        $lastModified = strtotime('2010-10-10 10:10:10');
        $ifModifiedSince = gmdate('D, d M Y H:i:s', $lastModified - 300).' GMT';

        $asset->expects($this->any())
            ->method('getFilters')
            ->will($this->returnValue(array()));
        $this->am->expects($this->any())
            ->method('has')
            ->with($name)
            ->will($this->returnValue(true));
        $this->am->expects($this->any())
            ->method('get')
            ->with($name)
            ->will($this->returnValue($asset));
        $this->am->expects($this->any())
            ->method('getLastModified')
            ->with($asset)
            ->will($this->returnValue($lastModified));
        $this->headers->expects($this->any())
            ->method('get')
            ->with('If-Modified-Since')
            ->will($this->returnValue($ifModifiedSince));
        $this->cache->expects($this->any())
            ->method('has')
            ->with($this->isType('string'))
            ->will($this->returnValue(false));
        $asset->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($content));

        $response = $this->controller->render($this->request, $name);

        $this->assertEquals(200, $response->getStatusCode(), '->render() sends an OK response when If-Modified-Since is stale');
        $this->assertEquals($content, $response->getContent(), '->render() sends the dumped asset as the response content');
    }

    public function testRenderETagFresh()
    {
        $asset = $this->getMockBuilder('Assetic\\Asset\\AssetInterface')->getMock();

        $name = 'foo';
        $formula = array(array('js/core.js'), array(), array(''));
        $etag = md5(serialize($formula + array('last_modified' => null)));

        $asset->expects($this->any())
            ->method('getFilters')
            ->will($this->returnValue(array()));
        $this->am->expects($this->any())
            ->method('has')
            ->with($name)
            ->will($this->returnValue(true));
        $this->am->expects($this->any())
            ->method('get')
            ->with($name)
            ->will($this->returnValue($asset));
        $this->am->expects($this->any())
            ->method('hasFormula')
            ->with($name)
            ->will($this->returnValue(true));
        $this->am->expects($this->any())
            ->method('getFormula')
            ->with($name)
            ->will($this->returnValue($formula));
        $this->request->expects($this->any())
            ->method('getETags')
            ->will($this->returnValue(array('"'.$etag.'"')));
        $asset->expects($this->never())
            ->method('dump');

        $response = $this->controller->render($this->request, $name);

        $this->assertEquals(304, $response->getStatusCode(), '->render() sends a Not Modified response when If-None-Match is fresh');
    }

    public function testRenderETagStale()
    {
        $asset = $this->getMockBuilder('Assetic\\Asset\\AssetInterface')->getMock();

        $name = 'foo';
        $content = '==ASSET_CONTENT==';
        $formula = array(array('js/core.js'), array(), array(''));

        $asset->expects($this->any())
            ->method('getFilters')
            ->will($this->returnValue(array()));
        $this->am->expects($this->once())
            ->method('has')
            ->with($name)
            ->will($this->returnValue(true));
        $this->am->expects($this->once())
            ->method('get')
            ->with($name)
            ->will($this->returnValue($asset));
        $this->am->expects($this->once())
            ->method('hasFormula')
            ->with($name)
            ->will($this->returnValue(true));
        $this->am->expects($this->once())
            ->method('getFormula')
            ->with($name)
            ->will($this->returnValue($formula));
        $this->request->expects($this->once())
            ->method('getETags')
            ->will($this->returnValue(array('"123"')));
        $asset->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($content));

        $response = $this->controller->render($this->request, $name);

        $this->assertEquals(200, $response->getStatusCode(), '->render() sends an OK response when If-None-Match is stale');
        $this->assertEquals($content, $response->getContent(), '->render() sends the dumped asset as the response content');
    }
}
