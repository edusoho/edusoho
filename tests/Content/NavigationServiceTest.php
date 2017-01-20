<?php

namespace Tests\Content;

use Biz\BaseTestCase;
use Biz\Content\Service\NavigationService;
use Biz\Course\Service\CourseService;
use Biz\User\Service\UserService;
use Topxia\Service\Course\MaterialService;

;

class NavigationServiceTest extends BaseTestCase
{

    public function testGetNavigation()
    {
        $this->assertNull(null);
    }

    public function testCreateNavigation()
    {
        $fileds = array(
            'name'     => '测试导航',
            'parentId' => 0,
            'url'      => 'http://baidu.com',
            'isOpen'   => 1,
            'isNewWin' => 0,
            'type'     => 'top'
        );

        $navigation = $this->getNavigationService()->createNavigation($fileds);
        $this->assertNotNull($navigation);
    }

    public function testUpdateNavigationsSequenceByIds()
    {
        $ids = array('1', '2', '3');
        $this->getNavigationService()->updateNavigationsSequenceByIds($ids);
    }

    /**
     * @return NavigationService
     */
    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content:NavigationService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course:MaterialService');
    }
}