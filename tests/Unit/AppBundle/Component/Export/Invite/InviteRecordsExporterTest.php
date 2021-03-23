<?php

namespace Tests\Unit\AppBundle\Component\Export\Invite;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Export\Invite\InviteRecordsExporter;
use Biz\BaseTestCase;

class InviteRecordsExporterTest extends BaseTestCase
{
    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:InviteRecordService',
            [
                [
                    'functionName' => 'searchRecords',
                    'returnValue' => [
                       [
                            'inviteUserId' => 1,
                            'invitedUserId' => 2,
                            'amount' => 2,
                            'coinAmount' => 3,
                            'cashAmount' => 4,
                            'inviteTime' => 444,
                        ],
                        [
                            'inviteUserId' => 2,
                            'invitedUserId' => 1,
                            'amount' => 2,
                            'coinAmount' => 3,
                            'cashAmount' => 4,
                            'inviteTime' => 444,
                        ],
                    ],
                    'withParams' => [
                    ],
                ],
                [
                    'functionName' => 'getAllUsersByRecords',
                    'returnValue' => [
                        '1' => [
                            'id' => 1,
                            'nickname' => 'wo',
                            'inviteCode' => 'wowowo',
                        ],
                        '2' => [
                            'id' => 2,
                            'nickname' => 'la',
                            'inviteCode' => 'lalala',
                        ],
                    ],
                    'withParams' => [
                    ],
                ],
            ]
        );

        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), []);
        $content = $expoter->getContent(0, 10);
        $this->assertArrayEquals([
            'wo',
            'la',
            2,
            3,
            4,
            'wowowo',
            '1970-01-01 08:07:24',
        ], $content[0]);

        $this->assertArrayEquals([
            'la',
            'wo',
            2,
            3,
            4,
            'lalala',
            '1970-01-01 08:07:24',
        ], $content[1]);
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:InviteRecordService',
            [
                [
                    'functionName' => 'countRecords',
                    'returnValue' => 10,
                    'withParams' => [
                    ],
                ],
            ]
        );
        $conditions = [
            'nickname' => 'aa',
            'startDate' => '2014-1-1',
            'endDate' => '2014-1-12',
            'userId' => 1,
        ];
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUserByNickname',
                    'returnValue' => ['id' => 2],
                    'withParams' => [
                    ],
                ],
            ]
        );
        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), $conditions);
        $count = $expoter->getCount();
        $this->assertEquals(10, $expoter->getCount());
    }

    public function testExportDataByRecord()
    {
        $record = [
            'inviteUserId' => 1,
            'invitedUserId' => 2,
            'amount' => 2,
            'coinAmount' => 3,
            'cashAmount' => 4,
            'inviteTime' => 444,
        ];

        $user = [
            '1' => [
                'id' => 1,
                'nickname' => 'wo',
                'inviteCode' => 'wowowo',
            ],
            '2' => [
                'id' => 2,
                'nickname' => 'la',
                'inviteCode' => 'lalala',
            ],
        ];

        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), []);
        $data = ReflectionUtils::invokeMethod($expoter, 'exportDataByRecord', [$record, $user]);

        $this->assertArrayEquals([
            'wo',
            'la',
            2,
            3,
            4,
            'wowowo',
            '1970-01-01 08:07:24',
        ], $data);
    }

    public function testBuildCondition()
    {
        $conditions = [
            'nickname' => 'aa',
            'startDate' => '2014-1-1',
            'endDate' => '2014-1-12',
            'userId' => 1,
        ];

        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUserByNickname',
                    'returnValue' => ['id' => 2],
                    'withParams' => [
                    ],
                ],
            ]
        );
        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), $conditions);

        $exportConditions = $expoter->buildCondition($conditions);
        $this->assertArrayEquals([
            'startDate' => '2014-1-1',
            'endDate' => '2014-1-12',
            'inviteUserId' => 2,
        ], $exportConditions);

        $this->assertEquals(true, empty($exportConditions['nickname']));
    }

    public function testGetTitles()
    {
        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), [
        ]);

        $title = [
            'admin.operation_invite.invite_code_owner',
            'admin.operation_invite.register_user',
            'admin.operation_invite.payingUserTotalPrice_th',
            'admin.operation_invite.coinAmountPrice_th',
            'admin.operation_invite.amountPrice_th',
            'user.register.invite_code_label',
            'user.account.my_invite_code.invite_time',
        ];

        $this->assertArrayEquals($expoter->getTitles(), $title);
    }

    public function testCanExport()
    {
        $expoter = new InviteRecordsExporter(self::$appKernel->getContainer(), [
        ]);
        $this->assertEquals(true, $expoter->canExport());

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);

        $this->assertEquals(false, $expoter->canExport());
    }
}
