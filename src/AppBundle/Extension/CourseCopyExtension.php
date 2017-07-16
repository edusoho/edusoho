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
        $biz['course_copy'] = function ($biz) {
            $processes = $this->processNodes();
            $courseNodes = $this->generateCourseNodes();

            return new $processes['course']['class']($biz, $courseNodes);
        };

        $biz['classroom_course_copy'] = function ($biz) {
            $processes = $this->processNodes();
            $classroomNodes = $this->generateClassroomNodes();

            return new $processes['classroom_course']['class']($biz, $classroomNodes);
        };

        $biz['course_set_courses_copy'] = function ($biz) {
            $process = $this->processNodes();
            $courseSetCoursesNodes = $this->generateCourseSetCoursesCopy();

            return new $process['course_set_courses']['class']($biz, $courseSetCoursesNodes);
        };
    }

    protected function processNodes()
    {
        $processNodes['course'] = array(
            'class' => 'Biz\Course\Copy\Entry\CourseCopy',
        );
        $processNodes['classroom_course'] = array(
            'class' => 'Biz\Course\Copy\Entry\ClassroomCourseCopy',
        );
        $processNodes['course_set_courses'] = array(
            'class' => 'Biz\Course\Component\Clones\Entry\CourseSetCoursesCopy',
        );

        return $processNodes;
    }

    protected function generateCourseNodes()
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

    protected function generateClassroomNodes()
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

        );
    }
}
