<?php

namespace Biz\Course\Copy;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CourseCopyServiceProvider implements ServiceProviderInterface
{
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
