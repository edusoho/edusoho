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

        foreach ($copyNodes as $key => $copyNode) {
            $biz[$key] = function ($biz) use ($self, $copyNode) {
                $copyNode = call_user_func(array($self, $copyNode));
                $copyClass = $copyNode['class'];

                return new $copyClass($biz, $copyNode);
            };
        }
    }

    public function generateCourseNodes()
    {
        return array(
            'class' => 'Biz\Course\Copy\Entry\CourseCopy',
            'children' => array(
                'course-member' => array(
                    'class' => 'Biz\Course\Copy\Chain\CourseMemberCopy',
                ),
                'task' => array(
                    'class' => 'Biz\Course\Copy\Chain\TaskCopy',
                ),
            ),
        );
    }

    public function generateClassroomNodes()
    {
        return array(
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
                'task' => array(
                    'class' => 'Biz\Course\Copy\Chain\TaskCopy',
                    'priority' => 50,
                ),
            ),
        );
    }

    public function generateCourseSetCoursesCopy()
    {
        return array(
            'class' => 'Biz\Course\Copy\CourseSet\CourseSetCopy',
            'priority' => 100,
            'isCopy' => 0,
            'children' => array(
                'tag-owner' => array(
                    'class' => 'Biz\Taxonomy\Copy\TagOwnerCopy',
                    'priority' => 100,
                ),
                'material' => array(
                    'class' => 'Biz\Course\Copy\MaterialCopy',
                    'priority' => 90,
                ),
                'course-set-courses' => array(
                    'class' => 'Biz\Course\Copy\CourseSetCoursesCopy',
                    'priority' => 80,
                    'auto' => false,
                    'children' => array(
                        'course-member' => array(
                            'class' => 'Biz\Course\Copy\CourseMemberCopy',
                            'priority' => 100,
                        ),
                        'course-task' => array(
                            'class' => 'Biz\Task\Copy\CourseTaskCopy',
                            'priority' => 90,
                            'auto' => false,
                            'children' => array(
                                'course-chapter' => array(
                                    'class' => 'Biz\Course\Copy\CourseChapterCopy',
                                    'priority' => 100,
                                ),
                                'activity' => array(
                                    'class' => 'Biz\Activity\Copy\ActivityCopy',
                                    'priority' => 90,
                                    'auto' => false,
                                    'children' => array(
                                        'activity-material' => array(
                                            'class' => 'Biz\Activity\Copy\ActivityMaterialCopy',
                                            'priority' => 100,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
