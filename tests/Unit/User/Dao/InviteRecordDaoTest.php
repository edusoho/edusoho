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

        $res = $this->getDao()->findByInviteUserIds(array(1, 3, 5, 7, 8));
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

    public function testSumCouponRateByInviteUserId()
    {
        $coupon = array(
            'id' => 1,
            'code' => 'test',
            'type' => 'minus',
            'status' => 'unused',
            'rate' => 10,
            'deadline' => '123',
            'createdTime' => '123',
        );
        $coupon = $this->getCouponDao()->create($coupon);

        $coupon['id'] = 5;
        $coupon['rate'] = 5;
        $coupon = $this->getCouponDao()->create($coupon);

        $coupon['id'] = 9;
        $coupon['rate'] = 9;
        $coupon = $this->getCouponDao()->create($coupon);

        $defaultInvite = $this->getDefaultMockFields();
        $this->getDao()->create($defaultInvite);
        $this->assertEquals(10, $this->getDao()->sumCouponRateByInviteUserId(1));

        $defaultInvite['inviteUserCardId'] = 5;
        $this->getDao()->create($defaultInvite);
        $this->assertEquals(15, $this->getDao()->sumCouponRateByInviteUserId(1));
        $this->assertEquals(0, $this->getDao()->sumCouponRateByInviteUserId(5));

        $defaultInvite['inviteUserCardId'] = 9;
        $this->getDao()->create($defaultInvite);
        $this->assertEquals(24, $this->getDao()->sumCouponRateByInviteUserId(1));
    }

    private function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
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
