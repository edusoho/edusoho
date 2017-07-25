<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CopyExtension extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $biz)
    {
        $self = $this;

        $copyNodes = array(
            'course_copy' => 'generateCourseNodes',
            'classroom_course_copy' => 'generateClassroomNodes',
            'course_set_courses_copy' => 'generateCourseSetCoursesCopy',
        );

        foreach ($copyNodes as $key => $copyNodes) {
            $biz[$key] = function ($biz) use ($self, $copyNodes) {
                $courseNodes = call_user_func(array($self, $copyNodes));
                $CopyClass = reset($courseNodes);
                $CopyClass = $CopyClass['class'];

                return new $CopyClass($biz, $courseNodes);
            };
        }
    }

    public function generateCourseNodes()
    {
        return array(
            'course' => array(
                'class' => 'Biz\Course\Copy\Entry\CourseCopy',
                'children' => array(
                    'course-member' => array(
                        'class' => 'Biz\Course\Copy\Chain\CourseMemberCopy',
                    ),
                    'task' => array(
                        'class' => 'Biz\Course\Copy\Chain\TaskCopy',
                    ),
                ),
            ),
        );
    }

    public function generateClassroomNodes()
    {
        return array(
            'classroom_course' => array(
                'class' => 'Biz\Course\Copy\Entry\ClassroomCourseCopy',
                'children' => array(
                    'material' => array(
                        'class' => 'Biz\Course\Copy\Chain\CourseMaterialCopy',
                        'priority' => 100,
                    ),
                    'course-member' => array(
                        'class' => 'Biz\Course\Copy\Chain\CourseMemberCopy',
                        'priority' => 90,
                    ),
                    'classroom-teacher' => array(
                        'class' => 'Biz\Course\Copy\Chain\ClassroomTeacherCopy',
                        'priority' => 80,
                    ),
                    'courseset-question' => array(
                        'class' => 'Biz\Course\Copy\Chain\CourseSetQuestionCopy',
                        'priority' => 70,
                    ),
                    'courseset-testpaper' => array(
                        'class' => 'Biz\Course\Copy\Chain\CourseSetTestpaperCopy',
                        'priority' => 60,
                    ),
                    'task' => array(
                        'class' => 'Biz\Course\Copy\Chain\TaskCopy',
                        'priority' => 50,
                    ),
                ),
            ),
        );
    }

    protected function generateCourseSetCoursesCopy()
    {
        return array(
            'course-set' => array(
                'class' => 'Biz\Course\Copy\CourseSet\CourseSetCopy',
                'priority' => 100,
                'isCopy' => 0,
                'children' => array(
                    'question' => array(
                        'class' => 'Biz\Question\Copy\QuestionCopy',
                    ),
                    'testpaper' => array(
                        'class' => 'Biz\Testpaper\Copy\TestpapersCopy',
                    ),
                    'tag-owner' => array(
                        'class' => 'Biz\Taxonomy\Copy\TagOwnerCopy',
                        'priority' => 100,
                    ),
                    'material' => array(
                        'class' => 'Biz\Course\Copy\MaterialCopy',
                        'priority' => 90,
                    ),
                    'courseset-courses' => array(
                        'class' => 'Biz\Course\Copy\CourseSetCoursesCopy',
                        'priority' => 80,
                        'children' => array(
                            'course-member' => array(
                                'class' => 'Biz\Course\Copy\CourseMemberCopy',
                                'priority' => 100,
                            ),
                            'course-task' => array(
                                'class' => 'Biz\Task\Copy\CourseTaskCopy',
                                'priority' => 90,
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
