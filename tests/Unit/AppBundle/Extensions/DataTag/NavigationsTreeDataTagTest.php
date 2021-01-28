<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\NavigationsTreeDataTag;

class NavigationsTreeDataTagTest extends BaseTestCase
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
            'parentId' => $navigation1['id'],
        ));
        $navigation3 = $this->getNavigationService()->createNavigation(array(
            'name' => 'navigation4',
            'url' => 'http://www.edusoho.com',
            'type' => 'top',
            'isOpen' => 0,
            'isNewWin' => 0,
            'parentId' => $navigation1['id'],
        ));
        $datatag = new NavigationsTreeDataTag();
        $navigations = $datatag->getData(array());
        $this->assertEquals(1, count($navigations));
        $this->assertEquals(1, count($navigations[$navigation1['id']]['children']));
    }

    protected function getNavigationService()
    {
        return $this->createService('Content:NavigationService');
    }
}
