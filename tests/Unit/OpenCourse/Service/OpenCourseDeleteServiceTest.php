<?php

namespace Tests\Unit\OpenCourse\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;

class OpenCourseDeleteServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testDeleteWithNoExistedType()
    {
        $this->getOpenCourseDeleteService()->delete(1, 2);
    }

    public function testDeleteWithLessons()
    {
        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            [
                [
                    'functionName' => 'getCourse',
                    'withParams' => [123],
                    'returnValue' => [
                        'id' => 123,
                        'title' => 'course_title',
                    ],
                ],
                [
                    'functionName' => 'countLessons',
                    'withParams' => [['courseId' => 123]],
                    'returnValue' => 2,
                ],
                [
                    'functionName' => 'updateCourse',
                    'withParams' => [123, ['lessonNum' => 2]],
                ],
            ]
        );

        $openCourseLessonDao = $this->mockBiz(
            'OpenCourse:OpenCourseLessonDao',
            [
                [
                    'functionName' => 'count',
                    'withParams' => [['courseId' => 123]],
                    'returnValue' => 2,
                ],
                [
                    'functionName' => 'search',
                    'withParams' => [
                        ['courseId' => 123],
                        ['createdTime' => 'desc'],
                        0,
                        500,
                    ],
                    'returnValue' => [
                        [
                            'id' => 1111,
                            'mediaId' => 12312,
                            'courseId' => 123,
                        ],
                        [
                            'id' => 1112,
                            'mediaId' => 12313,
                            'courseId' => 123,
                        ],
                    ],
                ],
                [
                    'functionName' => 'delete',
                    'withParams' => [1111],
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'delete',
                    'withParams' => [1112],
                    'returnValue' => 0,
                ],
            ]
        );

        $uploadFileService = $this->mockBiz(
            'File:UploadFileService',
            [
                [
                    'functionName' => 'waveUploadFile',
                    'withParams' => [12312, 'usedCount', -1],
                ],
                [
                    'functionName' => 'waveUploadFile',
                    'withParams' => [12313, 'usedCount', -1],
                ],
                [
                    'functionName' => 'getFile',
                    'withParams' => [12312],
                ],
                [
                    'functionName' => 'getFile',
                    'withParams' => [12313],
                ],
            ]
        );

        $logService = $this->mockBiz(
            'System:LogService',
            [
                [
                    'functionName' => 'info',
                    'withParams' => [
                        'open_course',
                        'delete_lesson',
                        '删除公开课《course_title》(#123)的所有课时',
                    ],
                ],
            ]
        );

        $materialService = $this->mockBiz(
            'Course:MaterialService',
            [
                [
                    'functionName' => 'searchMaterials',
                    'withParams' => [
                        [
                            'courseId' => 123,
                            'lessonId' => 1111,
                            'type' => 'openCourse',
                        ],
                        ['createdTime' => 'DESC'],
                        0,
                        PHP_INT_MAX,
                    ],
                ],
                [
                    'functionName' => 'searchMaterials',
                    'withParams' => [
                        [
                            'courseId' => 123,
                            'lessonId' => 1112,
                            'type' => 'openCourse',
                        ],
                        ['createdTime' => 'DESC'],
                        0,
                        PHP_INT_MAX,
                    ],
                ],
            ]
        );

        $result = $this->getOpenCourseDeleteService()->delete(123, 'lessons');

        $openCourseService->shouldHaveReceived('getCourse');
        $openCourseService->shouldHaveReceived('countLessons');
        $openCourseService->shouldHaveReceived('updateCourse');
        $openCourseLessonDao->shouldHaveReceived('count');
        $openCourseLessonDao->shouldHaveReceived('search');
        $openCourseLessonDao->shouldHaveReceived('delete');
        $uploadFileService->shouldHaveReceived('waveUploadFile');
        $materialService->shouldHaveReceived('searchMaterials');
        $logService->shouldHaveReceived('info');

        $this->assertEquals(1, $result);
    }

    public function testDeleteMembers()
    {
        $openCourseDao = $this->mockBiz(
            'OpenCourse:OpenCourseMemberDao',
            [
                [
                    'functionName' => 'count',
                    'withParams' => [['courseId' => 123]],
                    'returnValue' => 3,
                ],
                [
                    'functionName' => 'search',
                    'withParams' => [['courseId' => 123], ['createdTime' => 'desc'], 0, 500],
                    'returnValue' => [
                        ['id' => 22220], ['id' => 22221], ['id' => 22222],
                    ],
                ],
                [
                    'functionName' => 'delete',
                    'withParams' => [22220],
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'delete',
                    'withParams' => [22221],
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'delete',
                    'withParams' => [22222],
                    'returnValue' => 1,
                ],
            ]
        );

        $logService = $this->mockBiz(
            'System:LogService',
            [
                [
                    'functionName' => 'info',
                    'withParams' => [
                        'open_course',
                        'delete_member',
                        '删除公开课《course_title》(#123)的成员',
                    ],
                ],
            ]
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getOpenCourseDeleteService(),
            'deleteMembers',
            [['title' => 'course_title', 'id' => 123]]
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
            'OpenCourse:OpenCourseRecommendedDao',
            [
                [
                    'functionName' => 'count',
                    'withParams' => [['openCourseId' => 123]],
                    'returnValue' => 3,
                ],
                [
                    'functionName' => 'search',
                    'withParams' => [['openCourseId' => 123], ['createdTime' => 'desc'], 0, 500],
                    'returnValue' => [
                        ['id' => 22220], ['id' => 22221], ['id' => 22222],
                    ],
                ],
                [
                    'functionName' => 'delete',
                    'withParams' => [22220],
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'delete',
                    'withParams' => [22221],
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'delete',
                    'withParams' => [22222],
                    'returnValue' => 1,
                ],
            ]
        );

        $logService = $this->mockBiz(
            'System:LogService',
            [
                [
                    'functionName' => 'info',
                    'withParams' => [
                        'open_course',
                        'delete_recommend_course',
                        '删除公开课《course_title》(#123)的所有推荐课程',
                    ],
                ],
            ]
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getOpenCourseDeleteService(),
            'deleteRecommend',
            [['title' => 'course_title', 'id' => 123]]
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
            [
                [
                    'functionName' => 'countMaterials',
                    'withParams' => [['courseId' => 123, 'type' => 'openCourse']],
                    'returnValue' => 3,
                ],
                [
                    'functionName' => 'searchMaterials',
                    'withParams' => [['courseId' => 123, 'type' => 'openCourse'], ['createdTime' => 'DESC'], 0, 3],
                    'returnValue' => [
                        ['id' => 22220], ['id' => 22221], ['id' => 22222],
                    ],
                ],
                [
                    'functionName' => 'deleteMaterial',
                    'withParams' => [123, 22220],
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'deleteMaterial',
                    'withParams' => [123, 22221],
                    'returnValue' => 1,
                ],
                [
                    'functionName' => 'deleteMaterial',
                    'withParams' => [123, 22222],
                    'returnValue' => 1,
                ],
            ]
        );

        $logService = $this->mockBiz(
            'System:LogService',
            [
                [
                    'functionName' => 'info',
                    'withParams' => [
                        'open_course',
                        'delete_material',
                        '删除公开课《course_title》(#123)的所有课时资料',
                    ],
                ],
            ]
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getOpenCourseDeleteService(),
            'deleteMaterials',
            [['title' => 'course_title', 'id' => 123]]
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
            [
                [
                    'functionName' => 'delete',
                    'withParams' => [123],
                ],
            ]
        );

        $logService = $this->mockBiz(
            'System:LogService',
            [
                [
                    'functionName' => 'info',
                    'withParams' => [
                        'open_course',
                        'delete_course',
                        '删除公开课《course_title》(#123)',
                    ],
                ],
            ]
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getOpenCourseDeleteService(),
            'deleteCourse',
            [['title' => 'course_title', 'id' => 123]]
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
