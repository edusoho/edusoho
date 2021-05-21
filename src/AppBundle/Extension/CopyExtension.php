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

        $copyNodes = [
            'course_copy' => 'generateCourseNodes',
            'classroom_course_copy' => 'generateClassroomNodes',
            'course_set_courses_copy' => 'generateCourseSetCoursesCopy',
            'multi_class_copy' => 'generateMultiClassCopy',
        ];

        foreach ($copyNodes as $key => $copyNode) {
            $biz[$key] = function ($biz) use ($self, $copyNode) {
                $copyNode = call_user_func([$self, $copyNode]);
                $copyClass = $copyNode['class'];

                return new $copyClass($biz, $copyNode);
            };
        }
    }

    public function generateCourseNodes()
    {
        return [
            'class' => 'Biz\Course\Copy\Entry\CourseCopy',
            'children' => [
                'course-member' => [
                    'class' => 'Biz\Course\Copy\Chain\CourseMemberCopy',
                ],
                'task' => [
                    'class' => 'Biz\Course\Copy\Chain\TaskCopy',
                ],
            ],
        ];
    }

    public function generateClassroomNodes()
    {
        return [
            'class' => 'Biz\Course\Copy\Entry\ClassroomCourseCopy',
            'children' => [
                'material' => [
                    'class' => 'Biz\Course\Copy\Chain\CourseMaterialCopy',
                    'priority' => 100,
                ],
                'course-member' => [
                    'class' => 'Biz\Course\Copy\Chain\CourseMemberCopy',
                    'priority' => 90,
                ],
                'classroom-teacher' => [
                    'class' => 'Biz\Course\Copy\Chain\ClassroomTeacherCopy',
                    'priority' => 80,
                ],
                'task' => [
                    'class' => 'Biz\Course\Copy\Chain\TaskCopy',
                    'priority' => 50,
                ],
            ],
        ];
    }

    public function generateCourseSetCoursesCopy()
    {
        return [
            'class' => 'Biz\Course\Copy\CourseSet\CourseSetCopy',
            'priority' => 100,
            'isCopy' => 0,
            'children' => [
                'tag-owner' => [
                    'class' => 'Biz\Taxonomy\Copy\TagOwnerCopy',
                    'priority' => 100,
                ],
                'material' => [
                    'class' => 'Biz\Course\Copy\MaterialCopy',
                    'priority' => 90,
                ],
                'course-set-courses' => [
                    'class' => 'Biz\Course\Copy\CourseSetCoursesCopy',
                    'priority' => 80,
                    'auto' => false,
                    'children' => [
                        'course-member' => [
                            'class' => 'Biz\Course\Copy\CourseMemberCopy',
                            'priority' => 100,
                        ],
                        'course-task' => [
                            'class' => 'Biz\Task\Copy\CourseTaskCopy',
                            'priority' => 90,
                            'auto' => false,
                            'children' => [
                                'course-chapter' => [
                                    'class' => 'Biz\Course\Copy\CourseChapterCopy',
                                    'priority' => 100,
                                ],
                                'activity' => [
                                    'class' => 'Biz\Activity\Copy\ActivityCopy',
                                    'priority' => 90,
                                    'auto' => false,
                                    'children' => [
                                        'activity-material' => [
                                            'class' => 'Biz\Activity\Copy\ActivityMaterialCopy',
                                            'priority' => 100,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function generateMultiClassCopy()
    {
        return [
            'class' => 'Biz\MultiClass\Copy\MultiClass\MultiClassCopy',
        ];
    }
}
