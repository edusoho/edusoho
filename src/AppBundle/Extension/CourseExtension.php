<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CourseExtension extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $this->registerCourseCopyChain($container);
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
            'otherCourse' => array(
                'uri' => 'AppBundle:Course/Course:otherCourse',
                'renderType' => 'render',
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
                        'name' => '话题',
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
                    'reviews' => array(
                        'name' => '评价',
                        'number' => 'ratingNum',
                        'content' => 'AppBundle:Course/Course:reviews',
                    ),
                    'notes' => array(
                        'name' => '笔记',
                        'number' => 'noteNum',
                        'content' => 'AppBundle:Course/Course:notes',
                    ),
                ),
                'widgets' => $forGuestWidgets,
            ),
        );
    }

    protected function registerCourseCopyChain($container)
    {
        $chains = array(
            'course-set' => array(
                'clz' => 'Biz\Course\Copy\Impl\CourseSetCopy',
                'children' => array(
                    'course-set-testpaper' => array(
                        'clz' => 'Biz\Course\Copy\Impl\CourseSetTestpaperCopy',
                    ),
                    'course' => array(
                        'clz' => 'Biz\Course\Copy\Impl\CourseCopy',
                        'children' => array(
                            'task' => array(
                                'clz' => 'Biz\Course\Copy\Impl\TaskCopy',
                            ),
                        ),
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
