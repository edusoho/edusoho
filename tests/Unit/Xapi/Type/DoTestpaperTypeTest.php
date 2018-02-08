<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\DoTestpaperType;

class DoTestpaperTypeTest extends BaseTestCase
{
    public function testPackage()
    {
        $testpaperService = $this->mockBiz('Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'getTestpaper',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'score' => 12,
                    ),
                ),
                array(
                    'functionName' => 'getTestpaperResult',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'testId' => 1,
                        'courseId' => 2,
                        'courseSetId' => 3,
                        'passedStatus' => 'passed',
                        'score' => 10,
                        'paperName' => 'test paper',
                    ),
                ),
            )
        );

        $courseService = $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'withParams' => array(2),
                    'returnValue' => array(
                        'id' => 2,
                        'title' => 'course title',
                        'courseSetId' => 3,
                    ),
                ),
            )
        );

        $courseSetService = $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'withParams' => array(3),
                    'returnValue' => array(
                        'id' => 3,
                        'title' => 'course set title',
                        'subtitle' => 'course set subtitle',
                    ),
                ),
            )
        );

        $type = new DoTestpaperType();
        $type->setBiz($this->biz);
        $packageInfo = $type->package(array(
            'target_id' => 1,
            'user_id' => 12121,
            'uuid' => '123123123dse',
            'occur_time' => time(),
        ));

        $testpaperService->shouldHaveReceived('getTestpaperResult');
        $testpaperService->shouldHaveReceived('getTestpaper');
        $courseService->shouldHaveReceived('getCourse');
        $courseSetService->shouldHaveReceived('getCourseSet');

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('http://adlnet.gov/expapi/verbs/completed', $packageInfo['verb']['id']);
        $this->assertEquals(1, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }

    public function testPacpages()
    {
        $type = new DoTestpaperType();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array());

        $this->assertEmpty($packageInfo);
        $testpaperService = $this->mockBiz('Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'findTestpapersByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'score' => 12,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'findTestpaperResultsByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'testId' => 1,
                            'courseId' => 2,
                            'courseSetId' => 3,
                            'passedStatus' => 'passed',
                            'score' => 10,
                            'paperName' => 'test paper',
                        ),
                    ),
                ),
            )
        );

        $courseService = $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'findCoursesByIds',
                    'withParams' => array(array(2)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 2,
                            'title' => 'course title',
                            'courseSetId' => 3,
                        ),
                    ),
                ),
            )
        );

        $courseSetService = $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'findCourseSetsByIds',
                    'withParams' => array(array(3)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 3,
                            'title' => 'course set title',
                            'subtitle' => 'course set subtitle',
                        ),
                    ),
                ),
            )
        );

        $type = new DoTestpaperType();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array(
            array(
                'target_id' => 1,
                'user_id' => 12121,
                'uuid' => '123123123dse',
                'occur_time' => time(),
            ),
        ));

        $testpaperService->shouldHaveReceived('findTestpaperResultsByIds');
        $testpaperService->shouldHaveReceived('findTestpapersByIds');
        $courseService->shouldHaveReceived('findCoursesByIds');
        $courseSetService->shouldHaveReceived('findCourseSetsByIds');

        $packageInfo = reset($packageInfo);

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('http://adlnet.gov/expapi/verbs/completed', $packageInfo['verb']['id']);
        $this->assertEquals(1, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }
}
