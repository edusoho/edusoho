<?php

namespace Tests\Unit\OpenCourse;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class OpenCourseDeleteServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testDeleteWithNoExistedType()
    {
        $this->getOpenCourseDeleteService()->delete(1, 2);
    }

    public function testDeleteWithLessons()
    {
        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'withParams' => array(123),
                    'returnValue' => array(
                        'id' => 123,
                        'title' => 'course_title',
                    ),
                ),
                array(
                    'functionName' => 'countLessons',
                    'withParams' => array(array('courseId' => 123)),
                    'returnValue' => 2,
                ),
                array(
                    'functionName' => 'updateCourse',
                    'withParams' => array(123, array('lessonNum' => 2)),
                ),
            )
        );

        $openCourseLessonDao = $this->mockBiz(
            'OpenCourse:OpenCourseLessonDao',
            array(
                array(
                    'functionName' => 'count',
                    'withParams' => array(array('courseId' => 123)),
                    'returnValue' => 2,
                ),
                array(
                    'functionName' => 'search',
                    'withParams' => array(
                        array('courseId' => 123),
                        array('createdTime' => 'desc'),
                        0,
                        500,
                    ),
                    'returnValue' => array(
                        array(
                            'id' => 1111,
                            'mediaId' => 12312,
                            'courseId' => 123,
                        ),
                        array(
                            'id' => 1112,
                            'mediaId' => 12313,
                            'courseId' => 123,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(1111),
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(1112),
                    'returnValue' => 0,
                ),
            )
        );

        $uploadFileService = $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'waveUploadFile',
                    'withParams' => array(12312, 'usedCount', -1),
                ),
                array(
                    'functionName' => 'waveUploadFile',
                    'withParams' => array(12313, 'usedCount', -1),
                ),
                array(
                    'functionName' => 'getFile',
                    'withParams' => array(12312),
                ),
                array(
                    'functionName' => 'getFile',
                    'withParams' => array(12313),
                ),
            )
        );

        $logService = $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array(
                        'open_course',
                        'delete_lesson',
                        '删除公开课《course_title》(#123)的所有课时',
                    ),
                ),
            )
        );

        $materialService = $this->mockBiz(
            'Course:MaterialService',
            array(
                array(
                    'functionName' => 'searchMaterials',
                    'withParams' => array(
                        array(
                            'courseId' => 123,
                            'lessonId' => 1111,
                            'type' => 'openCourse',
                        ),
                        array('createdTime' => 'DESC'),
                        0,
                        PHP_INT_MAX,
                    ),
                ),
                array(
                    'functionName' => 'searchMaterials',
                    'withParams' => array(
                        array(
                            'courseId' => 123,
                            'lessonId' => 1112,
                            'type' => 'openCourse',
                        ),
                        array('createdTime' => 'DESC'),
                        0,
                        PHP_INT_MAX,
                    ),
                ),
            )
        );

        $result = $this->getOpenCourseDeleteService()->delete(123, 'lessons');

        $openCourseService->shouldHaveReceived('getCourse');
        $openCourseService->shouldHaveReceived('countLessons');
        $openCourseService->shouldHaveReceived('updateCourse');
        $openCourseLessonDao->shouldHaveReceived('count');
        $openCourseLessonDao->shouldHaveReceived('search');
        $openCourseLessonDao->shouldHaveReceived('delete');
        $uploadFileService->shouldHaveReceived('waveUploadFile');
        $uploadFileService->shouldHaveReceived('getFile');
        $materialService->shouldHaveReceived('searchMaterials');
        $logService->shouldHaveReceived('info');

        $this->assertEquals(1, $result);
    }

    public function testDeleteMembers()
    {
        $openCourseDao = $this->mockBiz(
            'OpenCourse:OpenCourseMemberDao',
            array(
                array(
                    'functionName' => 'count',
                    'withParams' => array(array('courseId' => 123)),
                    'returnValue' => 3,
                ),
                array(
                    'functionName' => 'search',
                    'withParams' => array(array('courseId' => 123), array('createdTime' => 'desc'), 0, 500),
                    'returnValue' => array(
                        array('id' => 22220), array('id' => 22221), array('id' => 22222),
                    ),
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(22220),
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(22221),
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(22222),
                    'returnValue' => 1,
                ),
            )
        );

        $logService = $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array(
                        'open_course',
                        'delete_member',
                        '删除公开课《course_title》(#123)的成员',
                    ),
                ),
            )
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getOpenCourseDeleteService(),
            'deleteMembers',
            array(array('title' => 'course_title', 'id' => 123))
        );

        $openCourseDao->shouldHaveReceived('count');
        $openCourseDao->shouldHaveReceived('search');
        $openCourseDao->shouldHaveReceived('delete');
        $logService->shouldHaveReceived('info');

        $this->assertEquals(3, $result);
    }

    public function testDeleteRecommend()
    {
        $recommendCourseDao = $this->mockBiz(
            'OpenCourse:RecommendedCourseDao',
            array(
                array(
                    'functionName' => 'count',
                    'withParams' => array(array('openCourseId' => 123)),
                    'returnValue' => 3,
                ),
                array(
                    'functionName' => 'search',
                    'withParams' => array(array('openCourseId' => 123), array('createdTime' => 'desc'), 0, 500),
                    'returnValue' => array(
                        array('id' => 22220), array('id' => 22221), array('id' => 22222),
                    ),
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(22220),
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(22221),
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(22222),
                    'returnValue' => 1,
                ),
            )
        );

        $logService = $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array(
                        'open_course',
                        'delete_recommend_course',
                        '删除公开课《course_title》(#123)的所有推荐课程',
                    ),
                ),
            )
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getOpenCourseDeleteService(),
            'deleteRecommend',
            array(array('title' => 'course_title', 'id' => 123))
        );

        $recommendCourseDao->shouldHaveReceived('count');
        $recommendCourseDao->shouldHaveReceived('search');
        $recommendCourseDao->shouldHaveReceived('delete');
        $logService->shouldHaveReceived('info');

        $this->assertEquals(3, $result);
    }

    public function testDeleteMaterials()
    {
        $materialService = $this->mockBiz(
            'Course:MaterialService',
            array(
                array(
                    'functionName' => 'countMaterials',
                    'withParams' => array(array('courseId' => 123, 'type' => 'openCourse')),
                    'returnValue' => 3,
                ),
                array(
                    'functionName' => 'searchMaterials',
                    'withParams' => array(array('courseId' => 123, 'type' => 'openCourse'), array('createdTime' => 'DESC'), 0, 3),
                    'returnValue' => array(
                        array('id' => 22220), array('id' => 22221), array('id' => 22222),
                    ),
                ),
                array(
                    'functionName' => 'deleteMaterial',
                    'withParams' => array(123, 22220),
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'deleteMaterial',
                    'withParams' => array(123, 22221),
                    'returnValue' => 1,
                ),
                array(
                    'functionName' => 'deleteMaterial',
                    'withParams' => array(123, 22222),
                    'returnValue' => 1,
                ),
            )
        );

        $logService = $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array(
                        'open_course',
                        'delete_material',
                        '删除公开课《course_title》(#123)的所有课时资料',
                    ),
                ),
            )
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getOpenCourseDeleteService(),
            'deleteMaterials',
            array(array('title' => 'course_title', 'id' => 123))
        );

        $materialService->shouldHaveReceived('countMaterials');
        $materialService->shouldHaveReceived('searchMaterials');
        $materialService->shouldHaveReceived('deleteMaterial');
        $logService->shouldHaveReceived('info');

        $this->assertEquals(3, $result);
    }

    public function testDeleteCourse()
    {
        $openCourseDao = $this->mockBiz(
            'OpenCourse:OpenCourseDao',
            array(
                array(
                    'functionName' => 'delete',
                    'withParams' => array(123),
                ),
            )
        );

        $logService = $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array(
                        'open_course',
                        'delete_course',
                        '删除公开课《course_title》(#123)',
                    ),
                ),
            )
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getOpenCourseDeleteService(),
            'deleteCourse',
            array(array('title' => 'course_title', 'id' => 123))
        );

        $openCourseDao->shouldHaveReceived('delete');
        $logService->shouldHaveReceived('info');

        $this->assertEquals(0, $result);
    }

    protected function getOpenCourseDeleteService()
    {
        return $this->createService('OpenCourse:OpenCourseDeleteService');
    }
}
