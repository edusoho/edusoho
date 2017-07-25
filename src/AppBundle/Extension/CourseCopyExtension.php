<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CourseCopyExtension extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $biz)
    {
        $self = $this;
        $biz['course_copy'] = function ($biz) use ($self) {
            $processes = $self->processNodes();
            $courseNodes = $self->generateCourseNodes();

            return new $processes['course']['class']($biz, $courseNodes);
        };

        $biz['classroom_course_copy'] = function ($biz) use ($self) {
            $processes = $self->processNodes();
            $classroomNodes = $self->generateClassroomNodes();

            return new $processes['classroom_course']['class']($biz, $classroomNodes);
        };

        $biz['course_set_courses_copy'] = function ($biz) {
            $process = $this->processNodes();
            $courseSetCoursesNodes = $this->generateCourseSetCoursesCopy();

            return new $process['course_set_courses']['class']($biz, $courseSetCoursesNodes);
        };
    }

    public function processNodes()
    {
        $processNodes['course'] = array(
            'class' => 'Biz\Course\Copy\Entry\CourseCopy',
        );
        $processNodes['classroom_course'] = array(
            'class' => 'Biz\Course\Copy\Entry\ClassroomCourseCopy',
        );
        $processNodes['course_set_courses'] = array(
            'class' => 'Biz\Course\Copy\CourseSet\CourseSetCopy',
        );

        return $processNodes;
    }

    public function generateCourseNodes()
    {
        return array(
            'course-member' => array(
                'class' => 'Biz\Course\Copy\Chain\CourseMemberCopy',
                'priority' => 100,
            ),
            'task' => array(
                'class' => 'Biz\Course\Copy\Chain\TaskCopy',
                'priority' => 90,
            ),
        );
    }

    public function generateClassroomNodes()
    {
        return array(
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
        );
    }

    protected function generateCourseSetCoursesCopy()
    {
        return array(
            'course-set' => array(
                'class' => 'Biz\Course\Copy\CourseSetCopy',
                'priority' => 100,
                'children' => array(
                    'tag-owner' => array(
                        'class' => 'Biz\Taxonomy\Copy\TagOwnerCopy',
                        'priority' => 100,
                    ),
                    'courseset-material' => array(
                        'class' => 'Biz\Course\Copy\CourseSetMaterialCopy',
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
//            'course-set' => array(
//                'class' => 'Biz\Course\Copy\CourseSet\CourseSetCopy',
//                'isCopy' => 0,
//            ),
        );
    }
}
