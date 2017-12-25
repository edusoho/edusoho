<?php

namespace Tests\Unit\Component\Export\Invite;

use Biz\BaseTestCase;
use AppBundle\Component\Export\Invite\InviteRecordsExporter;
use AppBundle\Common\ReflectionUtils;

class InviteRecordsExporterTest extends BaseTestCase
{
    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:InviteRecordService',
            array(
                array(
                    'functionName' => 'searchRecords',
                    'returnValue' => array(
                       array(
                            'inviteUserId' => 1,
                            'invitedUserId' => 2,
                            'amount' => 2,
                            'coinAmount' => 3,
                            'cashAmount' => 4,
                            'inviteTime' => 444,
                        ),
                        array(
                            'inviteUserId' => 2,
                            'invitedUserId' => 1,
                            'amount' => 2,
                            'coinAmount' => 3,
                            'cashAmount' => 4,
                            'inviteTime' => 444,
                        ),
                    ),
                    'withParams' => array(
                    ),
                ),
                array(
                    'functionName' => 'getAllUsersByRecords',
                    'returnValue' => array(
                        '1' => array(
                            'id' => 1,
                            'nickname' => 'wo',
                            'inviteCode' => 'wowowo',
                        ),
                        '2' => array(
                            'id' => 2,
                            'nickname' => 'la',
                            'inviteCode' => 'lalala',
                        ),
                    ),
                    'withParams' => array(
                    ),
                ),
            )
        );

        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), array());
        $content = $expoter->getContent(0, 10);
        $this->assertArrayEquals(array(
            'wo',
            'la',
            2,
            3,
            4,
            'wowowo',
            '1970-01-01 08:07:24',
        ), $content[0]);

        $this->assertArrayEquals(array(
            'la',
            'wo',
            2,
            3,
            4,
            'lalala',
            '1970-01-01 08:07:24',
        ), $content[1]);
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:InviteRecordService',
            array(
                array(
                    'functionName' => 'countRecords',
                    'returnValue' => 10,
                    'withParams' => array(
                    ),
                ),
            )
        );
        $conditions = array(
            'nickname' => 'aa',
            'startDate' => '2014-1-1',
            'endDate' => '2014-1-12',
            'userId' => 1,
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByNickname',
                    'returnValue' => array('id' => 2),
                    'withParams' => array(
                    ),
                ),
            )
        );
        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), $conditions);
        $count = $expoter->getCount();
        $this->assertEquals(10, $expoter->getCount());
    }

    public function testExportDataByRecord()
    {
        $record = array(
            'inviteUserId' => 1,
            'invitedUserId' => 2,
            'amount' => 2,
            'coinAmount' => 3,
            'cashAmount' => 4,
            'inviteTime' => 444,
        );

        $user = array(
            '1' => array(
                'id' => 1,
                'nickname' => 'wo',
                'inviteCode' => 'wowowo',
            ),
            '2' => array(
                'id' => 2,
                'nickname' => 'la',
                'inviteCode' => 'lalala',
            ),
        );

        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), array());
        $data = ReflectionUtils::invokeMethod($expoter, 'exportDataByRecord', array($record, $user));

        $this->assertArrayEquals(array(
            'wo',
            'la',
            2,
            3,
            4,
            'wowowo',
            '1970-01-01 08:07:24',
        ), $data);
    }

    public function testBuildCondition()
    {
        $conditions = array(
            'nickname' => 'aa',
            'startDate' => '2014-1-1',
            'endDate' => '2014-1-12',
            'userId' => 1,
        );

        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByNickname',
                    'returnValue' => array('id' => 2),
                    'withParams' => array(
                    ),
                ),
            )
        );
        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), $conditions);

        $exportConditions = $expoter->buildCondition($conditions);
        $this->assertArrayEquals(array(
            'startDate' => '2014-1-1',
            'endDate' => '2014-1-12',
            'inviteUserId' => 2,
        ), $exportConditions);

        $this->assertEquals(true, empty($exportConditions['nickname']));
    }

    public function testGetTitles()
    {
        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), array(
        ));

        $title = array(
            'admin.operation_invite.invite_code_owner',
            'admin.operation_invite.register_user',
            'admin.operation_invite.payingUserTotalPrice_th',
            'admin.operation_invite.coinAmountPrice_th',
            'admin.operation_invite.amountPrice_th',
            'user.register.invite_code_label',
            'user.account.my_invite_code.invite_time',
        );

        $title = $expoter->getTitles();

        $this->assertArrayEquals($expoter->getTitles(), $title);
    }

    public function testCanExport()
    {
        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), array(
        ));
        $this->assertEquals(true, $expoter->canExport());

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions(array());

        $this->assertEquals(false, $expoter->canExport());
    }
}
