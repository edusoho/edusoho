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
            'isOpen' => 1,
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

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
}