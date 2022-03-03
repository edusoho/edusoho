<?php

namespace Tests\Unit\AppBundle\Component\Export\Course;

use AppBundle\Component\Export\Course\StudentExporter;
use Biz\BaseTestCase;

class StudentExporterTest extends BaseTestCase
{
    public function testgetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new StudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
            'courseSetId' => 2,
        ]);

        $this->mockBiz(
            'Course:MemberService',
            [
                [
                    'functionName' => 'countMembers',
                    'returnValue' => 100,
                ],
            ]
        );

        $count = $expoter->getCount();
        $this->assertEquals($count, 100);
    }

    public function testBuildCondition()
    {
        $expoter = new StudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
            'courseSetId' => 2,
        ]);
        $conditions = $expoter->buildCondition(['courseId' => 10]);

        $this->assertArrayEquals([
            'courseId' => 10,
            'role' => 'student',
        ], $conditions);
    }

    public function testBuildParameter()
    {
        $expoter = new StudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
            'courseSetId' => 2,
        ]);
        $parameter = $expoter->buildParameter([
            'courseId' => 1,
            'courseSetId' => 2,
            'start' => 20,
            'fileName' => '/course/student.csv',
            'asd' => '123',
        ]);

        $this->assertArrayEquals([
            'start' => 20,
            'fileName' => 'student.csv',
            'courseId' => 1,
            'courseSetId' => 2,
        ], $parameter);
    }

    public function testGetTitles()
    {
        $this->mockUserField();
        $this->mockUserField(['field_title' => 'title2', 'field_enabled' => 1]);
        $expoter = new StudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
            'courseSetId' => 2,
        ]);

        $title = [
            'user.fields.username_label',
            'user.fields.email_label',
            'task.learn_data_detail.createdTime',
            'course.plan_task.study_rate',
            'course.plan_task.put_question',
            'student.report_card.homework',
            'course.testpaper_manage.testpaper',
            'user.fields.truename_label',
            'user.fields.gender_label',
            'user.fileds.qq',
            'user.fileds.wechat',
            'user.fields.mobile_label',
            'user.fields.company_label',
            'user.fields.career_label',
            'user.fields.title_label',
            'course.members_manage.export.field_learn_time',
            'student.profile.weibo',
        ];

        $this->assertArrayEquals($title, $expoter->getTitles());
    }

    public function testgetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockUserField(['field_title' => 'title', 'field_enabled' => 1]);
        $expoter = new StudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
            'courseSetId' => 2,
        ]);

        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'getCourse',
                    'returnValue' => [
                        'compulsoryTaskNum' => 10,
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'Course:MemberService',
            [
                [
                    'functionName' => 'searchMembers',
                    'returnValue' => [
                        [
                            'userId' => 1,
                            'createdTime' => 1,
                            'learnedCompulsoryTaskNum' => 2,
                            'learningTime' => 0,
                        ],
                        [
                            'userId' => 2,
                            'createdTime' => 33,
                            'learnedCompulsoryTaskNum' => 3,
                            'learningTime' => 0,
                        ],
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'findUsersByIds',
                    'returnValue' => [
                        1 => [
                            'id' => 1,
                            'nickname' => 'nickname',
                            'title' => 'title',
                            'email' => 'email',
                        ],
                        2 => [
                            'id' => 2,
                            'nickname' => 'nickname2',
                            'title' => 'title2',
                            'email' => 'email2',
                        ],
                    ],
                ],
                [
                    'functionName' => 'findUserProfilesByIds',
                    'returnValue' => [
                        1 => [
                            'id' => 1,
                            'qq' => 'qq',
                            'weibo' => 'weibo',
                            'weixin' => 'weixin',
                            'mobile' => 'mobile',
                            'company' => 'company',
                            'job' => 'job',
                            'gender' => 'female',
                            'truename' => 'truename',
                            'textField1' => '111',
                        ],
                        2 => [
                            'id' => 2,
                            'qq' => 'qq2',
                            'gender' => 'male',
                            'weibo' => 'weibo2',
                            'weixin' => 'weixin2',
                            'mobile' => 'mobile2',
                            'company' => 'company2',
                            'job' => 'job2',
                            'truename' => 'truename2',
                            'textField1' => '222',
                        ],
                    ],
                ],
            ]
        );

        $result = $expoter->getContent(0, 20);

        $this->assertArrayEquals([
            [
                'nickname',
                'email',
                '1970-1-01 08:00:01',
                '20%',
                '0',
                '0/0'."\t",
                '0/0'."\t",
                'truename',
                '女',
                'qq',
                'weixin',
                'mobile',
                'company',
                'job',
                'title',
                '-',
                'weibo',
                '111',
            ],
            [
                'nickname2',
                'email2',
                '1970-1-01 08:00:33',
                '30%',
                '0',
                '0/0'."\t",
                '0/0'."\t",
                'truename2',
                '男',
                'qq2',
                'weixin2',
                'mobile2',
                'company2',
                'job2',
                'title2',
                '-',
                'weibo2',
                '222',
            ],
        ], $result);
    }

    public function testCanExport()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new StudentExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
            'courseSetId' => 2,
        ]);
        $result = $expoter->canExport();
        $this->assertTrue($result);

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);
        $result = $expoter->canExport();
        $this->assertNotTrue($result);

        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'tryManageCourse',
                    'returnValue' => true,
                ],
            ]
        );

        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => true,
                ],
            ]
        );
        $result = $expoter->canExport();
        $this->assertNotTrue($result);
    }

    protected function mockUserField($fields = [])
    {
        $this->getUserFieldService()->addUserField(array_merge([
            'field_title' => 'title',
            'field_seq' => 1,
            'field_type' => 'text',
        ], $fields
        ));
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }
}
