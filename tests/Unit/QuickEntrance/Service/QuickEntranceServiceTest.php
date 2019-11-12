<?php

namespace Tests\Unit\QuickEntrance\Service;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\QuickEntrance\Service\QuickEntranceService;

class QuickEntranceServiceTest extends BaseTestCase
{
    public function testGetEntrancesByUserIdEmpty()
    {
        $fields = array(
            'userId' => $this->getCurrentUser()->getId(),
            'data' => array(
                'test_admin_entrance_empty',
            ),
        );

        $this->getQuickEntranceService()->createUserEntrance($fields);

        $result = $this->getQuickEntranceService()->getEntrancesByUserId($this->getCurrentUser()->getId());

        $this->assertEmpty($result);
    }

    public function testGetEntrancesByUserId()
    {
        $result = $this->getQuickEntranceService()->getEntrancesByUserId(0);
        $result = ArrayToolkit::index($result, 'code');

        $this->assertCount(7, $result);
        $this->assertArrayHasKey('admin_v2_course_show', $result);
        $this->assertArrayHasKey('admin_v2_block_manage', $result);
        $this->assertArrayHasKey('admin_v2_classroom', $result);
        $this->assertArrayHasKey('admin_v2_goods_order', $result);
        $this->assertArrayHasKey('admin_v2_marketing_coupon', $result);
        $this->assertArrayHasKey('admin_v2_user_show', $result);
        $this->assertArrayHasKey('admin_v2_user_coin', $result);

        $this->createCurrentUserInviteManageQuickEntrance();
        $result = $this->getQuickEntranceService()->getEntrancesByUserId($this->getCurrentUser()->getId());
        $result = ArrayToolkit::index($result, 'code');

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('admin_v2_operation_invite', $result);
    }

    public function testGetAllEntrancesWithoutUserId()
    {
        $result = $this->getQuickEntranceService()->getAllEntrances();

        $expected = array(
            'admin_v2_teach',
            'admin_v2_marketing',
            'admin_v2_operating',
            'admin_v2_user',
            'admin_v2_trade',
            'admin_v2_system',
        );

        $this->assertCount(6, $result);
        $this->assertEquals($expected, array_keys($result));
        $this->assertCount(9, $result['admin_v2_teach']['data']);
        $this->assertCount(4, $result['admin_v2_marketing']['data']);
        $this->assertCount(8, $result['admin_v2_operating']['data']);
        $this->assertCount(3, $result['admin_v2_user']['data']);
        $this->assertCount(4, $result['admin_v2_trade']['data']);
        $this->assertCount(5, $result['admin_v2_system']['data']);
    }

    public function testGetAllEntranceWithUserId()
    {
        $this->createCurrentUserInviteManageQuickEntrance();
        $result = $this->getQuickEntranceService()->getAllEntrances($this->getCurrentUser()->getId());

        $expected = array(
            'admin_v2_teach',
            'admin_v2_marketing',
            'admin_v2_operating',
            'admin_v2_user',
            'admin_v2_trade',
            'admin_v2_system',
        );

        $this->assertCount(6, $result);
        $this->assertEquals($expected, array_keys($result));
        $this->assertCount(9, $result['admin_v2_teach']['data']);
        $this->assertCount(4, $result['admin_v2_marketing']['data']);
        $this->assertCount(8, $result['admin_v2_operating']['data']);
        $this->assertCount(3, $result['admin_v2_user']['data']);
        $this->assertCount(4, $result['admin_v2_trade']['data']);
        $this->assertCount(5, $result['admin_v2_system']['data']);

        $marketingResult = ArrayToolkit::index($result['admin_v2_marketing']['data'], 'code');
        $this->assertTrue($marketingResult['admin_v2_operation_invite']['checked']);
        $this->assertFalse($marketingResult['admin_v2_marketing_coupon']['checked']);
    }

    public function testCreateUserEntrance()
    {
        $expected = array(
            array(
                'code' => 'admin_v2_operation_invite',
                'text' => '邀请管理',
                'icon' => 'invite',
                'link' => '/admin/v2/invite/record',
                'checked' => false,
                'class' => 'icon-color-yellow',
            ),
        );
        $result = $this->createCurrentUserInviteManageQuickEntrance();
        $this->assertCount(1, $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Fields invalid
     */
    public function testCreateUserEntrancesInvalidArgumentExceptionWithoutData()
    {
        $fields = array(
            'id' => $this->getCurrentUser()->getId(),
        );

        $this->getQuickEntranceService()->createUserEntrance($fields);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Fields invalid
     */
    public function testCreateUserEntrancesInvalidArgumentException()
    {
        $fields = array(
            'id' => $this->getCurrentUser()->getId(),
            'data' => array(
                'test_data_1',
                'test_data_2',
                'test_data_3',
                'test_data_4',
                'test_data_5',
                'test_data_6',
                'test_data_7',
                'test_data_8',
            ),
        );

        $this->getQuickEntranceService()->createUserEntrance($fields);
    }

    public function testUpdateUserEntrances()
    {
        $fields = array(
            'data' => array('admin_v2_marketing_coupon', 'admin_v2_operation_invite'),
        );
        $this->createCurrentUserInviteManageQuickEntrance();
        $before = $this->getQuickEntranceService()->getEntrancesByUserId($this->getCurrentUser()->getId());
        $this->getQuickEntranceService()->updateUserEntrances($this->getCurrentUser()->getId(), $fields);
        $after = $this->getQuickEntranceService()->getEntrancesByUserId($this->getCurrentUser()->getId());

        $this->assertNotEquals($before, $after);
        $this->assertCount(1, $before);
        $this->assertCount(2, $after);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Entrance data invalid.
     */
    public function testUpdateUserEntrancesWithInvalidArgumentException()
    {
        $this->createCurrentUserInviteManageQuickEntrance();
        $fields = array(
            'data' => array(
                'test_data_1',
                'test_data_2',
                'test_data_3',
                'test_data_4',
                'test_data_5',
                'test_data_6',
                'test_data_7',
                'test_data_8',
            ),
        );

        $this->getQuickEntranceService()->updateUserEntrances($this->getCurrentUser()->getId(), $fields);
    }

    private function createCurrentUserInviteManageQuickEntrance()
    {
        $fields = array(
            'userId' => $this->getCurrentUser()->getId(),
            'data' => array(
                'admin_v2_operation_invite',
            ),
        );

        return $this->getQuickEntranceService()->createUserEntrance($fields);
    }

    /**
     * @return QuickEntranceService
     */
    private function getQuickEntranceService()
    {
        return $this->createService('QuickEntrance:QuickEntranceService');
    }
}
