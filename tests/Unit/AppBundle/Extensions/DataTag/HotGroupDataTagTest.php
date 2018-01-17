<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\HotGroupDataTag;

class HotGroupDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCount()
    {
        $dataTag = new HotGroupDataTag();
        $announcement = $dataTag->getData(array());
    }

    public function testGetData()
    {
        $user = $this->getCurrentUser();

        $group1 = $this->getGroupService()->addGroup($user, array('title' => 'group1 title', 'about' => 'group about'));
        $group2 = $this->getGroupService()->addGroup($user, array('title' => 'group2 title', 'about' => 'group2 about'));
        $group3 = $this->getGroupService()->addGroup($user, array('title' => 'group3 title'));
        $group4 = $this->getGroupService()->addGroup($user, array('title' => 'group4 title', 'about' => 'group4 about'));
        $group5 = $this->getGroupService()->addGroup($user, array('title' => 'group5 title'));
        $this->getGroupService()->closeGroup($group5['id']);

        $datatag = new HotGroupDataTag();
        $datas = $datatag->getData(array('count' => 5));

        $this->assertEquals(4, count($datas));
    }

    protected function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }
}
