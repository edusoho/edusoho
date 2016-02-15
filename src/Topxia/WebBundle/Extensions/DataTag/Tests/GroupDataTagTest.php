<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\GroupDataTag;

class GroupDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $group = array(
            'title' => 'online',
            'about' => 'online test course 1'
        );
        $user = array(
            'id' => 1
        );

        $group = $this->getGroupService()->addGroup($user, $group);

        $datatag   = new GroupDataTag();
        $distGroup = $datatag->getData(array('groupId' => $group['id']));
        $this->assertEquals($group['id'], $distGroup['id']);
    }

    private function getGroupService()
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

}
