<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CourseExtension extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $biz)
    {
        $this->registerCourseCopyChain($biz);

        $biz['course_copy'] = function ($biz) {
            $chain = call_user_func($biz['course_copy.chains'], 'course');

            return new $chain['clz']($biz, 'course');
        };

        $biz['classroom_course_copy'] = function ($biz) {
            $chain = call_user_func($biz['course_copy.chains'], 'classroom-course');

            return new $chain['clz']($biz, 'classroom-course');
        };
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
                // 'uri'        => 'default/recommend-classroom.html.twig',
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
                        'name' => '目录',
                        'content' => 'AppBundle:Course/Course:tasks',
                    ),
                    'threads' => array(
                        'name' => '讨论区',
                        'number' => 'threadNum',
                        'content' => 'AppBundle:Course/Thread:index',
                    ),
                    'notes' => array(
                        'name' => '笔记',
                        'number' => 'noteNum',
                        'content' => 'AppBundle:Course/Course:notes',
                    ),
                    'material' => array(
                        'name' => '资料区',
                        'number' => 'materialNum',
                        'content' => 'AppBundle:Course/Material:index',
                    ),
                    'reviews' => array(
                        'name' => '评价',
                        'number' => 'ratingNum',
                        'content' => 'AppBundle:Course/Course:reviews',
                    ),
                    'summary' => array(
                        'name' => '介绍',
                        'content' => 'AppBundle:Course/Course:summary',
                    ),
                ),
                'widgets' => $forMemberWidgets,
            ),
            'for_guest' => array(
                'header' => 'AppBundle:Course/Course:header',
                'tabs' => array(
                    'summary' => array(
                        'name' => '介绍',
                        'content' => 'AppBundle:Course/Course:summary',
                    ),
                    'tasks' => array(
                        'name' => '目录',
                        'content' => 'AppBundle:Course/Course:tasks',
                    ),
                    'notes' => array(
                        'name' => '笔记',
                        'number' => 'noteNum',
                        'content' => 'AppBundle:Course/Course:notes',
                    ),
                    'reviews' => array(
                        'name' => '评价',
                        'number' => 'ratingNum',
                        'content' => 'AppBundle:Course/Course:reviews',
                    ),
                ),
                'widgets' => $forGuestWidgets,
            ),
        );
    }

    protected function registerCourseCopyChain($container)
    {
        $chains = array(
            'classroom-course' => array(
                'clz' => 'Biz\Course\Copy\Impl\ClassroomCourseCopy',
                'children' => array(
                    'material' => array(
                        'clz' => 'Biz\Course\Copy\Impl\CourseMaterialCopy',
                    ),
                    'course-member' => array(
                        'clz' => 'Biz\Course\Copy\Impl\CourseMemberCopy',
                    ),
                    'classroom-teacher' => array(
                        'clz' => 'Biz\Course\Copy\Impl\ClassroomTeacherCopy',
                    ),
                    'courseset-question' => array(
                        'clz' => 'Biz\Course\Copy\Impl\CourseSetQuestionCopy',
                    ),
                    'courseset-testpaper' => array(
                        'clz' => 'Biz\Course\Copy\Impl\CourseSetTestpaperCopy',
                    ),
                    'task' => array(
                        'clz' => 'Biz\Course\Copy\Impl\TaskCopy',
                    ),
                ),
            ),
            'course' => array(
                'clz' => 'Biz\Course\Copy\Impl\CourseCopy',
                'children' => array(
                    'course-member' => array(
                        'clz' => 'Biz\Course\Copy\Impl\CourseMemberCopy',
                    ),
                    'task' => array(
                        'clz' => 'Biz\Course\Copy\Impl\TaskCopy',
                    ),
                ),
            ),
        );

        $that = $this;
        //used for course/courseSet copy
        $container['course_copy.chains'] = function ($node) use ($that, $chains) {
            return function ($node) use ($that, $chains) {
                return $that->arrayWalk($chains, $node);
            };
        };
    }

    public function arrayWalk($array, $key)
    {
        if (!empty($array[$key])) {
            return $array[$key];
        }
        $result = array();
        foreach ($array as $k => $value) {
            if (!empty($value['children']) && empty($result)) {
                $result = $this->arrayWalk($value['children'], $key);
            }
        }

        return $result;
    }
}
