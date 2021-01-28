<?php

namespace Tests\Unit\Component\Export\Invite;

use Biz\BaseTestCase;
use AppBundle\Component\Export\Invite\InviteUserRecordsExporter;
use AppBundle\Common\ReflectionUtils;

class InviteUserRecordsExporterTest extends BaseTestCase
{
    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:InviteRecordService',
            array(
                array(
                    'functionName' => 'countInviteUser',
                    'returnValue' => 3,
                    'withParams' => array(
                    ),
                ),
            )
        );
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), array(
        ));

        $this->assertEquals(3, $expoter->getCount());
    }

    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:InviteRecordService',
            array(
                array(
                    'functionName' => 'searchRecordGroupByInviteUserId',
                    'returnValue' => array(
                        array(
                            'invitedUserNickname' => 3,
                            'countInvitedUserId' => 1,
                            'premiumUserCounts' => 1,
                            'amount' => 3,
                            'coinAmount' => 5,
                            'cashAmount' => 4,
                        ),
                        array(
                            'invitedUserNickname' => 3,
                            'countInvitedUserId' => 1,
                            'premiumUserCounts' => 1,
                            'amount' => 3,
                            'coinAmount' => 3,
                            'cashAmount' => 3,
                        ),
                    ),
                    'withParams' => array(
                    ),
                ),
            )
        );
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), array(
        ));
        $data = $expoter->getContent(0, 2);
        $this->assertArrayEquals(array(
            3, 1, 1, 3, 5, 4,
        ), $data[0]);
        $this->assertArrayEquals(array(
            3, 1, 1, 3, 3, 3,
        ), $data[1]);
    }

    public function testGetTitle()
    {
        $title = array(
            'admin.operation_invite.nickname_th',
            'admin.operation_invite.count_th',
            'admin.operation_invite.payingUserCount_th',
            'admin.operation_invite.payingUserTotalPrice_th',
            'admin.operation_invite.coinAmountPrice_th',
            'admin.operation_invite.amountPrice_th',
        );
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), array(
        ));

        $this->assertArrayEquals($title, $expoter->getTitles());
    }

    public function testCanExport()
    {
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), array(
        ));
        $this->assertEquals(true, $expoter->canExport());

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions(array());

        $this->assertEquals(false, $expoter->canExport());
    }

    public function testbuildCondition()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByNickname',
                    'returnValue' => array('id' => 3),
                    'withParams' => array(
                    ),
                ),
            )
        );
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), array(
        ));

        $condition = $expoter->buildCondition(array());
        $this->assertEquals(true, empty($condition));
        $condition = $expoter->buildCondition(array('nickname' => 'aa'));
        $this->assertEquals(3, $condition['inviteUserId']);
    }

    public function testGetUserRecordContent()
    {
        $data = array(
            array(
            'invitedUserNickname' => 3,
            'countInvitedUserId' => 1,
            'premiumUserCounts' => 1,
            'amount' => 3,
            'coinAmount' => 5,
            'cashAmount' => 4,
            ),
        );
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), array(
        ));
        $record = ReflectionUtils::invokeMethod($expoter, 'getUserRecordContent', array($data));

        $this->assertArrayEquals(array(
            3, 1, 1, 3, 5, 4,
        ), $record[0]);
    }
}
