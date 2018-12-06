<?php

namespace Tests\Unit\Content\Service;

use Biz\BaseTestCase;
use Biz\Content\Service\NavigationService;
use Biz\Course\Service\CourseService;
use Biz\User\Service\UserService;
use Topxia\Service\Course\MaterialService;

class NavigationServiceTest extends BaseTestCase
{
    public function testGetNavigation()
    {
        $fileds = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
        );
        $navigation = $this->getNavigationService()->createNavigation($fileds);

        $result = $this->getNavigationService()->getNavigation($navigation['id']);

        $this->assertEquals($navigation['id'], $result['id']);
        $this->assertEquals($navigation['name'], $result['name']);
    }

    public function testFindNavigations()
    {
        $fileds1 = array(
            'name' => '测试导航1',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
        );
        $navigation1 = $this->getNavigationService()->createNavigation($fileds1);

        $fileds2 = array(
            'name' => '测试导航2',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
        );
        $navigation2 = $this->getNavigationService()->createNavigation($fileds2);

        $fileds3 = array(
            'name' => '测试导航3',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
        );
        $navigation3 = $this->getNavigationService()->createNavigation($fileds3);

        $results = $this->getNavigationService()->findNavigations(0, 5);

        $this->assertEquals(3, count($results));
        $this->assertEquals($navigation1['id'], $results[0]['id']);
    }

    public function testGetNavigationsCount()
    {
        $fileds1 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 2,
        );
        $navigation1 = $this->getNavigationService()->createNavigation($fileds1);

        $fileds2 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 1,
        );
        $navigation2 = $this->getNavigationService()->createNavigation($fileds2);

        $fileds3 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation3 = $this->getNavigationService()->createNavigation($fileds3);

        $count = $this->getNavigationService()->getNavigationsCount();

        $this->assertEquals(3, $count);
    }

    public function testGetNavigationsCountByType()
    {
        $fileds1 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 2,
        );
        $navigation1 = $this->getNavigationService()->createNavigation($fileds1);

        $fileds2 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 1,
        );
        $navigation2 = $this->getNavigationService()->createNavigation($fileds2);

        $fileds3 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'bottom',
            'sequence' => 3,
        );
        $navigation3 = $this->getNavigationService()->createNavigation($fileds3);

        $results = $this->getNavigationService()->getNavigationsCountByType('top');
        $this->assertEquals(2, $results);
    }

    public function testFindNavigationsByType()
    {
        $fileds1 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 2,
        );
        $navigation1 = $this->getNavigationService()->createNavigation($fileds1);

        $fileds2 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 1,
        );
        $navigation2 = $this->getNavigationService()->createNavigation($fileds2);

        $fileds3 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'bottom',
            'sequence' => 3,
        );
        $navigation3 = $this->getNavigationService()->createNavigation($fileds3);

        $results = $this->getNavigationService()->findNavigationsByType('top', 0, 5);
        $this->assertEquals(2, count($results));
        $this->assertEquals($navigation1['id'], $results[0]['id']);
    }

    public function testSearchNavigationCount()
    {
        $user = $this->getCurrentuser();
        $fileds1 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 2,
        );
        $navigation1 = $this->getNavigationService()->createNavigation($fileds1);

        $fileds2 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 1,
        );
        $navigation2 = $this->getNavigationService()->createNavigation($fileds2);

        $fileds3 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'bottom',
            'sequence' => 3,
        );
        $navigation3 = $this->getNavigationService()->createNavigation($fileds3);

        $results = $this->getNavigationService()->searchNavigationCount(array());
        $this->assertEquals(3, $results);

        $this->mockSettingService();

        $results = $this->getNavigationService()->searchNavigationCount(array('type' => 'top'));
        $this->assertEquals(2, $results);
    }

    public function testSearchNavigations()
    {
        $fileds1 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 2,
        );
        $navigation1 = $this->getNavigationService()->createNavigation($fileds1);

        $fileds2 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 1,
        );
        $navigation2 = $this->getNavigationService()->createNavigation($fileds2);

        $fileds3 = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'bottom',
            'sequence' => 3,
        );
        $navigation3 = $this->getNavigationService()->createNavigation($fileds3);

        $results = $this->getNavigationService()->searchNavigations(array(), null, 0, 5);
        $this->assertEquals(3, count($results));
    }

    public function testGetOpenedNavigationsTreeByType()
    {
        $results = $this->getNavigationService()->getOpenedNavigationsTreeByType('top');
        $this->assertEmpty($results);

        $fileds1 = array(
            'name' => '测试导航1',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 2,
        );
        $navigation1 = $this->getNavigationService()->createNavigation($fileds1);

        $fileds2 = array(
            'name' => '测试导航2',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 1,
        );
        $navigation2 = $this->getNavigationService()->createNavigation($fileds2);

        $fileds3 = array(
            'name' => '测试导航3',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation3 = $this->getNavigationService()->createNavigation($fileds3);

        $fileds4 = array(
            'name' => '测试导航4',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 0,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation4 = $this->getNavigationService()->createNavigation($fileds4);

        $fileds5 = array(
            'name' => '测试导航5',
            'parentId' => $navigation4['id'],
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation5 = $this->getNavigationService()->createNavigation($fileds5);

        $fileds6 = array(
            'name' => '测试导航5',
            'parentId' => $navigation1['id'],
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation6 = $this->getNavigationService()->createNavigation($fileds6);

        $fileds6 = array(
            'name' => '测试导航5',
            'parentId' => 100,
            'url' => 'http://baidu.com',
            'isOpen' => 0,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation6 = $this->getNavigationService()->createNavigation($fileds6);

        $results = $this->getNavigationService()->getOpenedNavigationsTreeByType('top');
        $this->assertEquals(3, count($results));
        $this->assertEquals(1, count($results[$navigation1['id']]['children']));
    }

    public function testGetNavigationsListByType()
    {
        $fileds1 = array(
            'name' => '测试导航1',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 2,
        );
        $navigation1 = $this->getNavigationService()->createNavigation($fileds1);

        $fileds2 = array(
            'name' => '测试导航2',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 1,
        );
        $navigation2 = $this->getNavigationService()->createNavigation($fileds2);

        $fileds3 = array(
            'name' => '测试导航3',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation3 = $this->getNavigationService()->createNavigation($fileds3);

        $fileds4 = array(
            'name' => '测试导航4',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 0,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation4 = $this->getNavigationService()->createNavigation($fileds4);

        $fileds5 = array(
            'name' => '测试导航5',
            'parentId' => $navigation4['id'],
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation5 = $this->getNavigationService()->createNavigation($fileds5);

        $fileds6 = array(
            'name' => '测试导航5',
            'parentId' => $navigation1['id'],
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation6 = $this->getNavigationService()->createNavigation($fileds6);

        $fileds6 = array(
            'name' => '测试导航5',
            'parentId' => 100,
            'url' => 'http://baidu.com',
            'isOpen' => 0,
            'isNewWin' => 0,
            'type' => 'top',
            'sequence' => 3,
        );
        $navigation6 = $this->getNavigationService()->createNavigation($fileds6);

        $results = $this->getNavigationService()->getNavigationsListByType('top');
        $this->assertNotNull($results);
    }

    public function testCreateNavigation()
    {
        $fileds = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
        );

        $navigation = $this->getNavigationService()->createNavigation($fileds);
        $this->assertNotNull($navigation);

        $this->mockSettingService();

        $fileds2 = array(
            'name' => '测试导航',
            'parentId' => $navigation['id'],
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
        );

        $navigation2 = $this->getNavigationService()->createNavigation($fileds2);
        $this->assertNotNull($navigation);
        $this->assertEquals($navigation['orgId'], $navigation2['orgId']);
        $this->assertEquals($navigation['orgCode'], $navigation2['orgCode']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testCreateNavigationException()
    {
        $fileds = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
            'title' => '123',
        );

        $this->getNavigationService()->createNavigation($fileds);
    }

    public function updateNavigation($id, $fields)
    {
        $fileds = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
        );

        $navigation = $this->getNavigationService()->createNavigation($fileds);

        $update = array('name' => '测试导航修改', 'isOpen' => 0, 'id' => 3);
        $result = $this->getNavigationService()->updateNavigation($id, $update);

        $this->assertEquals($navigation['id'], $result['id']);
        $this->assertEquals($update['name'], $result['name']);
        $this->assertEquals($update['isOpen'], $result['isOpen']);
    }

    public function testUpdateNavigationsSequenceByIds()
    {
        $ids = array('1', '2', '3');
        $this->getNavigationService()->updateNavigationsSequenceByIds($ids);
    }

    public function testDeleteNavigation()
    {
        $fileds = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 1,
            'isNewWin' => 0,
            'type' => 'top',
        );

        $navigation = $this->getNavigationService()->createNavigation($fileds);
        $result = $this->getNavigationService()->getNavigation($navigation['id']);
        $this->assertEquals($navigation['id'], $result['id']);

        $this->getNavigationService()->deleteNavigation($navigation['id']);

        $result = $this->getNavigationService()->getNavigation($navigation['id']);
        $this->assertNull($result);
    }

    private function mockSettingService()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('enable_org' => 1),
            ),
        ));
    }

    /**
     * @return NavigationService
     */
    protected function getNavigationService()
    {
        return $this->createService('Content:NavigationService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }
}
