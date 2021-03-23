<?php

namespace Tests\Unit\AppBundle\Component\Export\Invite;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Export\Invite\InviteUserRecordsExporter;
use Biz\BaseTestCase;

class InviteUserRecordsExporterTest extends BaseTestCase
{
    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:InviteRecordService',
            [
                [
                    'functionName' => 'countInviteUser',
                    'returnValue' => 3,
                    'withParams' => [
                    ],
                ],
            ]
        );
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), [
        ]);

        $this->assertEquals(3, $expoter->getCount());
    }

    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:InviteRecordService',
            [
                [
                    'functionName' => 'searchRecordGroupByInviteUserId',
                    'returnValue' => [
                        [
                            'invitedUserNickname' => 3,
                            'countInvitedUserId' => 1,
                            'premiumUserCounts' => 1,
                            'amount' => 3,
                            'coinAmount' => 5,
                            'cashAmount' => 4,
                        ],
                        [
                            'invitedUserNickname' => 3,
                            'countInvitedUserId' => 1,
                            'premiumUserCounts' => 1,
                            'amount' => 3,
                            'coinAmount' => 3,
                            'cashAmount' => 3,
                        ],
                    ],
                    'withParams' => [
                    ],
                ],
            ]
        );
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), [
        ]);
        $data = $expoter->getContent(0, 2);
        $this->assertArrayEquals([
            3, 1, 1, 3, 5, 4,
        ], $data[0]);
        $this->assertArrayEquals([
            3, 1, 1, 3, 3, 3,
        ], $data[1]);
    }

    public function testGetTitle()
    {
        $title = [
            'admin.operation_invite.nickname_th',
            'admin.operation_invite.count_th',
            'admin.operation_invite.payingUserCount_th',
            'admin.operation_invite.payingUserTotalPrice_th',
            'admin.operation_invite.coinAmountPrice_th',
            'admin.operation_invite.amountPrice_th',
        ];
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), [
        ]);

        $this->assertArrayEquals($title, $expoter->getTitles());
    }

    public function testCanExport()
    {
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), [
        ]);
        $this->assertEquals(true, $expoter->canExport());

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);

        $this->assertEquals(false, $expoter->canExport());
    }

    public function testbuildCondition()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUserByNickname',
                    'returnValue' => ['id' => 3],
                    'withParams' => [
                    ],
                ],
            ]
        );
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), [
        ]);

        $condition = $expoter->buildCondition([]);
        $this->assertEquals(true, empty($condition));
        $condition = $expoter->buildCondition(['nickname' => 'aa']);
        $this->assertEquals(3, $condition['inviteUserId']);
    }

    public function testGetUserRecordContent()
    {
        $data = [
            [
            'invitedUserNickname' => 3,
            'countInvitedUserId' => 1,
            'premiumUserCounts' => 1,
            'amount' => 3,
            'coinAmount' => 5,
            'cashAmount' => 4,
            ],
        ];
        $expoter = new InviteUserRecordsExporter(self::$appKernel->getContainer(), [
        ]);
        $record = ReflectionUtils::invokeMethod($expoter, 'getUserRecordContent', [$data]);

        $this->assertArrayEquals([
            3, 1, 1, 3, 5, 4,
        ], $record[0]);
    }
}
