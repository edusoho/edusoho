<?php

namespace Tests\Unit\Component\Export\Invite;

use Biz\BaseTestCase;
use AppBundle\Component\Export\Course\StudentExporter;

class StudentExporterTest extends BaseTestCase
{
    public function testgetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new StudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
            'courseSetId' => 2,
        ));

        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'countMembers',
                    'returnValue' => 100,
                ),
            )
        );

        $count = $expoter->getCount();
        $this->assertEquals($count, 100);
    }

    public function testBuildCondition()
    {
        $expoter = new StudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
            'courseSetId' => 2,
        ));
        $conditions = $expoter->buildCondition(array('courseId' => 10));

        $this->assertArrayEquals(array(
            'courseId' => 10,
            'role' => 'student',
        ), $conditions);
    }

    public function testBuildParameter()
    {
        $expoter = new StudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
            'courseSetId' => 2,
        ));
        $parameter = $expoter->buildParameter(array(
            'courseId' => 1,
            'courseSetId' => 2,
            'start' => 20,
            'fileName' => '/course/student.csv',
            'asd' => '123',
        ));

        $this->assertArrayEquals(array(
            'start' => 20,
            'fileName' => 'student.csv',
            'courseId' => 1,
            'courseSetId' => 2,
        ), $parameter);
    }

    public function testGetTitles()
    {
        $this->mockUserField();
        $this->mockUserField(array('field_title' => 'title2', 'field_enabled' => 1));
        $expoter = new StudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
            'courseSetId' => 2,
        ));

        $title = array(
            'user.fields.username_label',
            'user.fields.email_label',
            'task.learn_data_detail.createdTime',
            'course.plan_task.study_rate',
            'user.fields.truename_label',
            'user.fields.gender_label',
            'user.fileds.qq',
            'user.fileds.wechat',
            'user.fields.mobile_label',
            'user.fields.company_label',
            'user.fields.career_label',
            'user.fields.title_label',
            'student.profile.weibo',
            'title2',
        );

        $this->assertArrayEquals($title, $expoter->getTitles());
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockUserField(array('field_title' => 'title', 'field_enabled' => 1));
        $expoter = new StudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
            'courseSetId' => 2,
        ));

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array(
                        'compulsoryTaskNum' => 10,
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'searchMembers',
                    'returnValue' => array(
                        array(
                            'userId' => 1,
                            'createdTime' => 1,
                            'learnedCompulsoryTaskNum' => 2,
                        ),
                        array(
                            'userId' => 2,
                            'createdTime' => 33,
                            'learnedCompulsoryTaskNum' => 3,
                        ),
                    ),
                ),
            )
        );

        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUsersByIds',
                    'returnValue' => array(
                        1 => array(
                            'id' => 1,
                            'nickname' => 'nickname',
                            'title' => 'title',
                            'email' => 'email',
                        ),
                        2 => array(
                            'id' => 2,
                            'nickname' => 'nickname2',
                            'title' => 'title2',
                            'email' => 'email2',
                        ),
                    ),
                ),
                array(
                    'functionName' => 'findUserProfilesByIds',
                    'returnValue' => array(
                        1 => array(
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
                        ),
                        2 => array(
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
                        ),
                    ),
                ),
            )
        );

        $result = $expoter->getContent(0, 20);

        $this->assertArrayEquals(array(
            array(
                'nickname',
                'email',
                '1970-1-01 08:00:01',
                '20%',
                'truename',
                '女',
                'qq',
                'weixin',
                'mobile',
                'company',
                'job',
                'title',
                'weibo',
                '111',
            ),
            array(
                'nickname2',
                'email2',
                '1970-1-01 08:00:33',
                '30%',
                'truename2',
                '男',
                'qq2',
                'weixin2',
                'mobile2',
                'company2',
                'job2',
                'title2',
                'weibo2',
                '222',
            ),
        ), $result);
    }

    public function testCanExport()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new StudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
            'courseSetId' => 2,
        ));
        $result = $expoter->canExport();
        $this->assertTrue($result);

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions(array());
        $result = $expoter->canExport();
        $this->assertNotTrue($result);

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => true,
                ),
            )
        );

        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => true,
                ),
            )
        );
        $result = $expoter->canExport();
        $this->assertNotTrue($result);
    }

    protected function mockUserField($fields = array())
    {
        $this->getUserFieldService()->addUserField(array_merge(array(
            'field_title' => 'title',
            'field_seq' => 1,
            'field_type' => 'text',
        ), $fields
        ));
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }
}
