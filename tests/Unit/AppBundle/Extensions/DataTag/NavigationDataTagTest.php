<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\NavigationDataTag;

class NavigationDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $navigation1 = $this->getNavigationService()->createNavigation(array(
            'name' => 'navigation1',
            'url' => 'http://www.edusoho.com',
            'type' => 'top',
            'isOpen' => 1,
            'isNewWin' => 0,
            'parentId' => '0',
        ));
        $navigation2 = $this->getNavigationService()->createNavigation(array(
            'name' => 'navigation2',
            'url' => 'http://www.edusoho.com',
            'type' => 'foot',
            'isOpen' => 1,
            'isNewWin' => 0,
            'parentId' => '0',
        ));
        $navigation3 = $this->getNavigationService()->createNavigation(array(
            'name' => 'navigation3',
            'url' => 'http://www.edusoho.com',
            'type' => 'top',
            'isOpen' => 1,
            'isNewWin' => 0,
            'parentId' => $navigation1,
        ));
        $datatag = new NavigationDataTag();
        $navigations = $datatag->getData(array('type' => 'top'));
        $this->assertEquals(2, count($navigations));
        $navigations = $datatag->getData(array('type' => 'foot'));
        $this->assertEquals(1, count($navigations));
    }

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content:NavigationService');
    }
}
