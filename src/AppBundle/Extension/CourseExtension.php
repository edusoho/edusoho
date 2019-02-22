<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Biz\Course\Service\CourseSetService;
use Biz\OpenCourse\Service\OpenCourseService;

class CourseExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // TODO: Implement register() method.
    }

    public function getCourseTypes()
    {
        return array(
            CourseSetService::NORMAL_TYPE => array(
                'template' => 'courseset-manage/normal/create-show.html.twig',
                'saveAction' => 'AppBundle:Course/CourseSetManage:saveCourse',
                'priority' => 30,
                'visible' => 1,
            ),
            CourseSetService::LIVE_TYPE => array(
                'template' => 'courseset-manage/live/create-show.html.twig',
                'saveAction' => 'AppBundle:Course/CourseSetManage:saveCourse',
                'priority' => 20,
                'visible' => 1,
            ),
            OpenCourseService::OPEN_TYPE => array(
                'template' => 'open-course-manage/open/create-show.html.twig',
                'saveAction' => 'AppBundle:OpenCourseManage:saveCourse',
                'priority' => 10,
                'visible' => 1,
            ),
            OpenCourseService::LIVE_OPEN_TYPE => array(
                'template' => 'open-course-manage/liveOpen/create-show.html.twig',
                'saveAction' => 'AppBundle:OpenCourseManage:saveCourse',
                'priority' => 0,
                'visible' => 1,
            ),
        );
    }

    public function getCourseShowMetas()
    {
        $widgets = array(
            //课程特色
            'characteristic' => array(
                'uri' => 'AppBundle:Course/Course:characteristic',
                'renderType' => 'render',
            ),
            //其他教学计划
            'otherCourses' => array(
                'uri' => 'AppBundle:Course/Course:otherCourses',
                'renderType' => 'render',
                'showMode' => 'course',
            ),
            //所属班级
            'belongClassroom' => array(
                'uri' => 'course/widgets/belong-classroom.html.twig',
                'renderType' => 'include',
                'showMode' => 'classroom', //班级课程才会显示
            ),
            //推荐班级
            'recommendClassroom' => array(
                'uri' => 'course/widgets/recommend-classroom.html.twig',
                'renderType' => 'include',
                'showMode' => 'course', //普通课程才会显示
            ),
            //教学团队
            'teachers' => array(
                'uri' => 'AppBundle:Course/Course:teachers',
                'renderType' => 'render',
            ),
            //最新学员
            'newestStudents' => array(
                'uri' => 'AppBundle:Course/Course:newestStudents',
                'renderType' => 'render',
            ),
            //学员动态
            'studentActivity' => array(
                'uri' => 'course/widgets/student-activity.html.twig',
                'renderType' => 'include',
            ),
        );

        $forGuestWidgets = array(
            'teachers' => $widgets['teachers'],
            'characteristic' => $widgets['characteristic'],
            'newestStudents' => $widgets['newestStudents'],
            'studentActivity' => $widgets['studentActivity'],
        );

        $forMemberWidgets = array(
            'otherCourses' => $widgets['otherCourses'],
            'belongClassroom' => $widgets['belongClassroom'],
            'teachers' => $widgets['teachers'],
            'newestStudents' => $widgets['newestStudents'],
            'studentActivity' => $widgets['studentActivity'],
        );

        return array(
            'for_member' => array(
                'header' => 'AppBundle:My/Course:headerForMember',
                'tabs' => array(
                    'tasks' => array(
                        'name' => 'course.tab.tasks',
                        'content' => 'AppBundle:My/Course:tasks',
                    ),
                    'discussion' => array(
                        'name' => 'course.tab.discussions',
                        'number' => 'discussionNum',
                        'content' => 'AppBundle:Course/Thread:index',
                    ),
                    'question' => array(
                        'name' => 'course.tab.questions',
                        'number' => 'questionNum',
                        'content' => 'AppBundle:Course/Thread:index',
                    ),
                    'notes' => array(
                        'name' => 'course.tab.notes',
                        'number' => 'noteNum',
                        'content' => 'AppBundle:Course/Course:notes',
                    ),
                    'material' => array(
                        'name' => 'course.tab.material',
                        'number' => 'materialNum',
                        'content' => 'AppBundle:Course/Material:index',
                    ),
                    'reviews' => array(
                        'name' => 'course.tab.reviews',
                        'number' => 'ratingNum',
                        'content' => 'AppBundle:Course/Course:reviews',
                    ),
                    'summary' => array(
                        'name' => 'course.tab.summary',
                        'content' => 'AppBundle:Course/Course:summary',
                    ),
                ),
                'widgets' => $forMemberWidgets,
            ),
            'for_guest' => array(
                'header' => 'AppBundle:Course/Course:header',
                'tabs' => array(
                    'summary' => array(
                        'name' => 'course.tab.summary',
                        'content' => 'AppBundle:Course/Course:summary',
                    ),
                    'tasks' => array(
                        'name' => 'course.tab.tasks',
                        'content' => 'AppBundle:Course/Course:tasks',
                    ),
                    'notes' => array(
                        'name' => 'course.tab.notes',
                        'number' => 'noteNum',
                        'content' => 'AppBundle:Course/Course:notes',
                    ),
                    'reviews' => array(
                        'name' => 'course.tab.reviews',
                        'number' => 'ratingNum',
                        'content' => 'AppBundle:Course/Course:reviews',
                    ),
                ),
                'widgets' => $forGuestWidgets,
            ),
        );
    }
}
