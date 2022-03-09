<?php

namespace AppBundle\Extension;

use Biz\Course\Service\CourseSetService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\System\Service\SettingService;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CourseExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // TODO: Implement register() method.
    }

    public function getCourseTypes()
    {
        return [
            CourseSetService::NORMAL_TYPE => [
                'template' => 'courseset-manage/normal/create-show.html.twig',
                'saveAction' => 'AppBundle:Course/CourseSetManage:saveCourse',
                'priority' => 30,
                'visible' => 1,
            ],
            CourseSetService::LIVE_TYPE => [
                'template' => 'courseset-manage/live/create-show.html.twig',
                'saveAction' => 'AppBundle:Course/CourseSetManage:saveCourse',
                'priority' => 20,
                'visible' => 1,
            ],
            OpenCourseService::OPEN_TYPE => [
                'template' => 'open-course-manage/open/create-show.html.twig',
                'saveAction' => 'AppBundle:OpenCourseManage:saveCourse',
                'priority' => 10,
                'visible' => 1,
            ],
            OpenCourseService::LIVE_OPEN_TYPE => [
                'template' => 'open-course-manage/liveOpen/create-show.html.twig',
                'saveAction' => 'AppBundle:OpenCourseManage:saveCourse',
                'priority' => 0,
                'visible' => 1,
            ],
        ];
    }

    public function getTaskTypes()
    {
        return [
            'audio' => [
                'name' => 'user.learn.statistics.lesson.audio',
            ],
            'discuss' => [
                'name' => 'user.learn.statistics.lesson.discuss',
            ],
            'doc' => [
                'name' => 'user.learn.statistics.lesson.doc',
            ],
            'download' => [
                'name' => 'user.learn.statistics.lesson.download',
            ],
            'exercise' => [
                'name' => 'user.learn.statistics.lesson.exercise',
            ],
            'flash' => [
                'name' => 'user.learn.statistics.lesson.flash',
            ],
            'homework' => [
                'name' => 'user.learn.statistics.lesson.homework',
            ],
            'live' => [
                'name' => 'user.learn.statistics.lesson.live',
            ],
            'ppt' => [
                'name' => 'user.learn.statistics.lesson.ppt',
            ],
            'testpaper' => [
                'name' => 'user.learn.statistics.lesson.testpaper',
            ],
            'text' => [
                'name' => 'user.learn.statistics.lesson.text',
            ],
            'video' => [
                'name' => 'user.learn.statistics.lesson.video',
            ],
        ];
    }

    public function getCourseShowMetas()
    {
        $widgets = [
            //课程特色
            'characteristic' => [
                'uri' => 'AppBundle:Course/Course:characteristic',
                'renderType' => 'render',
            ],
            //其他教学计划
            'otherCourses' => [
                'uri' => 'AppBundle:Course/Course:otherCourses',
                'renderType' => 'render',
                'showMode' => 'course',
            ],
            //所属班级
            'belongClassroom' => [
                'uri' => 'course/widgets/belong-classroom.html.twig',
                'renderType' => 'include',
                'showMode' => 'classroom', //班级课程才会显示
            ],
            //推荐班级
            'recommendClassroom' => [
                'uri' => 'course/widgets/recommend-classroom.html.twig',
                'renderType' => 'include',
                'showMode' => 'course', //普通课程才会显示
            ],
            //教学团队
            'teachers' => [
                'uri' => 'AppBundle:Course/Course:teachers',
                'renderType' => 'render',
            ],
            //最新学员
            'drainage' => [
                'uri' => 'AppBundle:Course/Course:drainage',
                'renderType' => 'render',
            ],
            'assistantInfo' => [
                'uri' => 'AppBundle:Course/Course:assistantInfo',
                'renderType' => 'render',
                'showMode' => 'course',
            ],
            //最新学员
            'newestStudents' => [
                'uri' => 'AppBundle:Course/Course:newestStudents',
                'renderType' => 'render',
            ],
            //学员动态
            'studentActivity' => [
                'uri' => 'course/widgets/student-activity.html.twig',
                'renderType' => 'include',
            ],
        ];

        $forGuestWidgets = [
            'teachers' => $widgets['teachers'],
            'drainage' => $widgets['drainage'],
            'characteristic' => $widgets['characteristic'],
            'newestStudents' => $widgets['newestStudents'],
            'studentActivity' => $widgets['studentActivity'],
        ];

        $forMemberWidgets = [
            'otherCourses' => $widgets['otherCourses'],
            'belongClassroom' => $widgets['belongClassroom'],
            'teachers' => $widgets['teachers'],
            'drainage' => $widgets['drainage'],
            'assistantInfo' => $widgets['assistantInfo'],
            'newestStudents' => $widgets['newestStudents'],
            'studentActivity' => $widgets['studentActivity'],
        ];

        return [
            'for_member' => [
                'header' => 'AppBundle:My/Course:headerForMember',
                'tabs' => $this->getTabs('forMember'),
                'widgets' => $forMemberWidgets,
            ],
            'for_guest' => [
                'header' => 'AppBundle:Course/Course:header',
                'tabs' => $this->getTabs('forGuest'),
                'widgets' => $forGuestWidgets,
            ],
        ];
    }

    protected function getTabs($for)
    {
        $courseSetting = $this->getSettingService()->get('course', []);

        $tabs = [
            'tasks' => [
                'name' => 'course.tab.tasks',
                'content' => 'forGuest' === $for ? 'AppBundle:Course/Course:tasks' : 'AppBundle:My/Course:tasks',
            ],
            'discussion' => [
                'name' => 'course.tab.discussions',
                'number' => 'discussionNum',
                'content' => 'AppBundle:Course/Thread:index',
            ],
            'question' => [
                'name' => 'course.tab.questions',
                'number' => 'questionNum',
                'content' => 'AppBundle:Course/Thread:index',
            ],
            'notes' => [
                'name' => 'course.tab.notes',
                'number' => 'noteNum',
                'content' => 'AppBundle:Course/Course:notes',
            ],
            'material' => [
                'name' => 'course.tab.material',
                'number' => 'materialNum',
                'content' => 'AppBundle:Course/Material:index',
            ],
            'reviews' => [
                'name' => 'course.tab.reviews',
                'number' => 'ratingNum',
                'content' => 'AppBundle:Course/Course:reviews',
            ],
            'summary' => [
                'name' => 'course.tab.summary',
                'content' => 'AppBundle:Course/Course:summary',
            ],
            'certificate' => [
                'name' => 'course.tab.certificate',
                'content' => 'AppBundle:Course/Course:certificate',
            ],
        ];

        if ('forGuest' == $for) {
            unset($tabs['material'], $tabs['discussion'], $tabs['question']);
        }

        if (isset($courseSetting['show_note']) && '0' == $courseSetting['show_note']) {
            unset($tabs['notes']);
        }

        if (isset($courseSetting['show_question']) && '0' == $courseSetting['show_question']) {
            unset($tabs['question']);
        }

        if (isset($courseSetting['show_discussion']) && '0' == $courseSetting['show_discussion']) {
            unset($tabs['discussion']);
        }

        if (isset($courseSetting['show_review']) && '0' == $courseSetting['show_review']) {
            unset($tabs['reviews']);
        }

        return $tabs;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
