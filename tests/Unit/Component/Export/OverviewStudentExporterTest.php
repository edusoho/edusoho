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
            'courseId' => 1,
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
            'courseId' => 1,
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