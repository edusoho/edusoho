<?php

namespace Tests\Unit\User\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class InviteRecordDaoTest extends BaseDaoTestCase
{
    public function testFindByInvitedUserIds()
    {
        $default = $this->getDefaultMockFields();
        $this->getDao()->create($default);
        $default['invitedUserId'] = 4;
        $this->getDao()->create($default);
        $default['invitedUserId'] = 3;
        $this->getDao()->create($default);

        $res = $this->getDao()->findByInvitedUserIds(array(2, 3, 4));
        $this->assertEquals(3, count($res));

        $res = $this->getDao()->findByInvitedUserIds(array(2, 3));
        $this->assertEquals(2, count($res));

        $res = $this->getDao()->findByInvitedUserIds(array(1, 3));
        $this->assertEquals(1, count($res));
    }

    public function testFindByInviteUserIds()
    {
        $default = $this->getDefaultMockFields();
        $this->getDao()->create($default);
        $default['inviteUserId'] = 3;
        $this->getDao()->create($default);
        $default['inviteUserId'] = 4;
        $this->getDao()->create($default);
        $default['inviteUserId'] = 5;
        $this->getDao()->create($default);

        $res = $this->getDao()->findByInviteUserIds(array(1, 3,5,7,8));
        $this->assertEquals(3, count($res));

        $res = $this->getDao()->findByInviteUserIds(array(10));
        $this->assertEquals(0, count($res));

        $res = $this->getDao()->findByInviteUserIds(array(3));
        $this->assertEquals(1, count($res));
    }

    public function testFindByInviteUserId()
    {
        $default = $this->getDefaultMockFields();
        $this->getDao()->create($default);
        $res = $this->getDao()->findByInviteUserId(3);
        $this->assertEquals(0, count($res));

        $res = $this->getDao()->findByInviteUserId(1);
        $this->assertEquals(1, count($res));
    }

    protected function getDefaultMockFields()
    {
        return array(
            'inviteUserId' => '1',
            'invitedUserId' => '2',
            'inviteTime' => time(),
            'inviteUserCardId' => 1,
            'invitedUserCardId' => 2,
        );
    }
}
