<?php
namespace Topxia\Service\Content\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Content\FileService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class NavigationServiceTest extends BaseTestCase
{   
    
    public function testGetNavigation()
    {
        $this->assertNull(null);
    }

    public function testCreateNavigation()
    {
        $fileds = array(
            'name' => '测试导航',
            'parentId' => 0,
            'url' => 'http://baidu.com',
            'isOpen' => 0,
            'isNewWin' => 0,
            'type' => 'top'
        );

        $navigation = $this->getNavigationService()->createNavigation($fileds);
        $this->assertNotNull($navigation);
    }

    public function testUpdateNavigationsSequenceByIds()
    {
        $ids = array('1', '2', '3');
        $this->getNavigationService()->updateNavigationsSequenceByIds($ids);
    }

    private function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
}