<?php

namespace Tests\Unit\Component\Activity;

use AppBundle\Component\Activity\ActivityProxy;
use AppBundle\Component\Activity\ActivityRuntimeContainer;
use Biz\BaseTestCase;
use Biz\User\Service\Impl\UserServiceImpl;
use Codeages\Biz\Framework\Dao\Connection;
use Codeages\Biz\Framework\Service\ServiceProxy;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class ActivityRuntimeContainerTest extends BaseTestCase
{
    public function testShowTwig()
    {
        $container = $this->getContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'twig_test',
        );
        $response = $container->get('activity_runtime_container')->show($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testShowHtml()
    {
        $container = $this->getContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $container->get('activity_runtime_container')->show($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testShowPhp()
    {
        $container = $this->getContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'php_test',
        );
        $response = $container->get('activity_runtime_container')->show($activity);
        $this->assertEquals('test title', $response);
    }

    public function testCreate()
    {
        $container = $this->getContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $container->get('activity_runtime_container')->create($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testContent()
    {
        $container = $this->getContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $container->get('activity_runtime_container')->content($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testUpdate()
    {
        $container = $this->getContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $container->get('activity_runtime_container')->update($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testFinish()
    {
        $container = $this->getContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $container->get('activity_runtime_container')->finish($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testGetDb()
    {
        $container = $this->getContainer();
        $result = $container->get('activity_runtime_container')->getDB();
        $this->assertTrue($result instanceof Connection);
    }

    public function testGetRequest()
    {
        $container = $this->getContainer();
        $result = $container->get('activity_runtime_container')->getRequest();
        $this->assertTrue($result instanceof Request);
    }

    public function testRenderRoute()
    {
        $container = $this->getContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $container->get('activity_runtime_container')->renderRoute($activity, 'show');
        $this->assertEquals('test title', $response->getContent());
    }

    public function testGetActivityProxy()
    {
        $container = $this->getContainer();
        $result = $container->get('activity_runtime_container')->getActivityProxy();
        $this->assertTrue($result instanceof ActivityProxy);
    }

    public function testGetInstance()
    {
        $container = $this->getContainer();
        $activityContainer = $container->get('activity_runtime_container');
        $result = $activityContainer::instance();
        $this->assertTrue($result instanceof ActivityRuntimeContainer);
    }

    public function testCreateService()
    {
        $container = $this->getContainer();
        $activityContainer = $container->get('activity_runtime_container');
        $result = $activityContainer->createService('User:UserService');
        $this->assertTrue($result instanceof ServiceProxy);
        $this->assertTrue($result->getClass() instanceof UserServiceImpl);
    }

    public function testCreateJsonResponse()
    {
        $container = $this->getContainer();
        $activityContainer = $container->get('activity_runtime_container');
        $result = $activityContainer->createJsonResponse(array('title' => 'test title'));
        $this->assertEquals(array('title' => 'test title'), json_decode($result->getContent(), true));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage You can not use the "render" method if the Templating Component or the Twig Bundle are not available.
     */
    public function testRender()
    {
        $container = new Container();
        $container->set('biz', $this->biz);
        $container->setParameter('edusoho.activities_dir', $this->getContainer()->getParameter('edusoho.activities_dir'));
        $container->set('activity_config_manager', $this->getContainer()->get('activity_config_manager'));
        $container->set('request', $this->getContainer()->get('request'));
        $activityContainer = new ActivityRuntimeContainer($container);
        $activityContainer->render('test.html', array());
    }
}