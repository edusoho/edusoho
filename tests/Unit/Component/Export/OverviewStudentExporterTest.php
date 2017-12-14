<?php

namespace Tests\Unit\Component\Export;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Export\Exporter;
use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Component\Export\Course\OverviewStudentExporter;

class OverviewStudentExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $biz = $this->getBiz();
        for ($i=0; $i <= 4; $i++) {
            $this->getTaskDao()->create(array('title' => 'test'.$i, 'type' => 'vedio','courseId' => 1, 'isOptional' => 0, 'status' => 'published', 'createdUserId' => 1));
        }
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1
        ));

        $title = $expoter->getTitles();
        $result = array(
            "task.learn_data_detail.nickname",
            "task.learn_data_detail.finished_rate",
            "test0",
            "test1",
            "test2",
            "test3",
            "test4",
        );

        $this->assertArrayEquals($result, $title);
    }

    public function testCanExport()
    {
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1
        ));
        $result = $expoter->canExport();
        $this->assertEquals(false, $result);

        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => true,
                    'withParams' => array(
                        1
                    ),
                ),
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('id' => 1),
                    'withParams' => array(
                        1
                    ),
                ),
            )
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
        ));

        $result = $expoter->canExport();
        $this->assertEquals(true, $result);
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:ReportService',
            array(
                array(
                    'functionName' => 'buildStudentDetailConditions',
                    'returnValue' => array('role' => 'student', 'courseId' => 1),
                    'withParams' => array(
                      array(
                            'courseId' => 1,
                        ),
                        1
                    ),
                ),
                array(
                    'functionName' => 'buildStudentDetailOrderBy',
                    'returnValue' => array('createdTime' => 'desc'),
                    'withParams' => array(
                      array(
                            'courseId' => 1,
                        )
                    ),
                ),                
            )
        );
        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'countMembers',
                    'returnValue' => 10,
                    'withParams' => array(
                       
                    ),
                )
            )
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
        ));

        $count = $expoter->getCount();
        $this->assertEquals(10, $count);
    }

    public function testBuildParameter()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:ReportService',
            array(
                array(
                    'functionName' => 'buildStudentDetailConditions',
                    'returnValue' => array('role' => 'student', 'courseId' => 1),
                    'withParams' => array(
                      array(
                            'courseId' => 1,
                            'start' => 1,
                        ),
                        1
                    ),
                ),
                array(
                    'functionName' => 'buildStudentDetailOrderBy',
                    'returnValue' => 'createdTime',
                    'withParams' => array(
                      array(
                            'courseId' => 1,
                            'start' => 1,
                        )
                    ),
                ),
            )
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
            'start' => 1,
        ));
        $parameter = $expoter->buildParameter(array(
            'courseId' => 1,
            'start' => 1,
        ));

        $this->assertEquals(1, $parameter['start']);
        $this->assertEquals('', $parameter['fileName']);
        $this->assertEquals(1, $parameter['courseId']);
        $this->assertEquals('createdTime', $parameter['orderBy']);
    }

    public function testBuildCondition()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:ReportService',
            array(
                array(
                    'functionName' => 'buildStudentDetailConditions',
                    'returnValue' => array('role' => 'student', 'courseId' => 1),
                    'withParams' => array(
                      array(
                            'courseId' => 1,
                            'start' => 1,
                        ),
                        1
                    ),
                ),
                array(
                    'functionName' => 'buildStudentDetailOrderBy',
                    'returnValue' => 'createdTime',
                    'withParams' => array(
                      array(
                            'courseId' => 1,
                            'start' => 1,
                        )
                    ),
                ),
            )
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
            'start' => 1,
        )); 

        $conditions = $expoter->buildCondition(array(
            'courseId' => 1,
            'start' => 1,
        )); 

        $this->assertArrayEquals(array('role' => 'student', 'courseId' => 1), $conditions);
    }

    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:ReportService',
            array(
                array(
                    'functionName' => 'buildStudentDetailConditions',
                    'returnValue' => array('role' => 'student', 'courseId' => 1),
                    'withParams' => array(
                      array(
                            'courseId' => 1,
                            'start' => 1,
                        ),
                        1
                    ),
                ),
                array(
                    'functionName' => 'buildStudentDetailOrderBy',
                    'returnValue' => 'createdTime',
                    'withParams' => array(
                      array(
                            'courseId' => 1,
                            'start' => 1,
                        )
                    ),
                ),
                array(
                    'functionName' => 'getStudentDetail',
                    'returnValue' => array(
                        array(
                            1 => array(
                                'id' => 1,
                                'nickname' => 'test1'
                            ),
                            2 => array(
                                'id' => 2,
                                'nickname' => 'test2'
                            ),
                        ),
                        array(
                            3 => array(
                                'id' => 3
                            ),
                            4 => array(
                                'id' => 4
                            ),
                        ),
                        array(
                            1 => array(
                                 3 => array(
                                     'status' => 'start'
                                 ),
                            ),
                            2 => array(
                                 4 => array(
                                     'status' => 'finish'
                                 ),
                                 
                            ),
                        )),
                ),
            )
        );
        

        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'searchMembers',
                    'returnValue' => array(
                        array('userId' => 1, 'learnedCompulsoryTaskNum' => 1),
                        array('userId' => 2, 'learnedCompulsoryTaskNum' => 2),
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'countTasks',
                    'returnValue' => 10,
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => true,
                ),
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('id' => 1, 'compulsoryTaskNum' => 2),
                ),
            )
        );
        $expoter = new OverviewStudentExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
            'start' => 1,
        ));

        $data = $expoter->getContent(0, 10);

        $this->assertArrayEquals(
            array(
                'test1', '50%', '学习中', '未开始'
            )
            , $data[0]
        );
        $this->assertArrayEquals(
            array(
                'test2', '100%', '未开始', '已完成'
            ), $data[1]
        );
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getTaskDao()
    {
        return $this->getBiz()->dao('Task:TaskDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}