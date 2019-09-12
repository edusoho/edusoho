<?php

namespace Tests\Unit\Component\Activity;

use AppBundle\Component\Activity\ActivityProxy;
use AppBundle\Component\Activity\ActivityRuntimeContainer;
use Biz\BaseTestCase;
use Biz\User\Service\Impl\UserServiceImpl;
use Codeages\Biz\Framework\Dao\Connection;
use Codeages\Biz\Framework\Service\ServiceProxy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

class ActivityRuntimeContainerTest extends BaseTestCase
{
    public function testShowTwig()
    {
        $activityContainer = $this->createRuntimeContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'twig_test',
        );
        $response = $activityContainer->show($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testShowHtml()
    {
        $activityContainer = $this->createRuntimeContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $activityContainer->show($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testShowPhp()
    {
        $activityContainer = $this->createRuntimeContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'php_test',
        );
        $response = $activityContainer->show($activity);
        $this->assertEquals('test title', $response);
    }

    public function testCreate()
    {
        $activityContainer = $this->createRuntimeContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $activityContainer->create($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testContent()
    {
        $activityContainer = $this->createRuntimeContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $activityContainer->content($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testUpdate()
    {
        $activityContainer = $this->createRuntimeContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $activityContainer->update($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testFinish()
    {
        $activityContainer = $this->createRuntimeContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $activityContainer->finish($activity);
        $this->assertEquals('test title', $response->getContent());
    }

    public function testGetDb()
    {
        $activityContainer = $this->createRuntimeContainer();
        $result = $activityContainer->getDB();
        $this->assertTrue($result instanceof Connection);
    }

    public function testGetRequest()
    {
        $activityContainer = $this->createRuntimeContainer();
        $result = $activityContainer->getRequest();
        $this->assertTrue($result instanceof Request);
    }

    public function testRenderRoute()
    {
        $activityContainer = $this->createRuntimeContainer();
        $activity = array(
            'title' => 'test title',
            'mediaType' => 'html_test',
        );
        $response = $activityContainer->renderRoute($activity, 'show');
        $this->assertEquals('test title', $response->getContent());
    }

    public function testGetActivityProxy()
    {
        $activityContainer = $this->createRuntimeContainer();
        $result = $activityContainer->getActivityProxy();
        $this->assertTrue($result instanceof ActivityProxy);
    }

    public function testGetInstance()
    {
        $activityContainer = $this->createRuntimeContainer();
        $result = $activityContainer::instance();
        $this->assertTrue($result instanceof ActivityRuntimeContainer);
    }

    public function testCreateService()
    {
        $activityContainer = $this->createRuntimeContainer();
        $result = $activityContainer->createService('User:UserService');
        $this->assertTrue($result instanceof ServiceProxy);
        $this->assertTrue($result->getClass() instanceof UserServiceImpl);
    }

    public function testCreateJsonResponse()
    {
        $activityContainer = $this->createRuntimeContainer();
        $result = $activityContainer->createJsonResponse(array('title' => 'test title'));
        $this->assertEquals(array('title' => 'test title'), json_decode($result->getContent(), true));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage You can not use the "render" method if the Templating Component or the Twig Bundle are not available.
     */
    public function testRender()
    {
        $container = new ContainerBuilder();
        $container->set('biz', $this->biz);
        $container->setParameter('edusoho.activities_dir', $this->getContainer()->getParameter('edusoho.activities_dir'));
        $container->set('activity_config_manager', $this->getContainer()->get('activity_config_manager'));
        $container->set('request', $this->getContainer()->get('request'));
        $activityContainer = new ActivityRuntimeContainer($container);
        $activityContainer->render('test.html', array());
    }

    public function createRuntimeContainer()
    {
        $container = $this->getContainer()->get('activity_runtime_container');
        $container->setActivitiesDir($this->getContainer()->getParameter('kernel.root_dir').'/../tests/Unit/AppBundle/Fixtures/activities');

        return $container;
    }
}
