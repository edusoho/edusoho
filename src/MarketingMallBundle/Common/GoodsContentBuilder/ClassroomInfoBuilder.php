<?php


namespace MarketingMallBundle\Common\GoodsContentBuilder;


use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;

class ClassroomInfoBuilder extends AbstractBuilder
{
    const CLASSROOM_ALLOWED_KEY = ['classroom_id', 'classroom_catalogue'];

    public function build($id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);
        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM);
        }

        return $this->buildClassroomData($classroom);
    }

    protected function buildClassroomData($classroom)
    {
        $courseIds = ArrayToolkit::column($this->getClassroomService()->findCoursesByClassroomId($classroom['id']), 'id');
        $classroom['classroom_id'] = $classroom['id'];
        foreach ($courseIds as $courseId) {
            $course = $this->getCourseDetailBuilder()->build($courseId);
            unset($course['course_ids']);
            $course['course_id'] = $courseId;
            $classroom['classroom_catalogue'][] = $course;
        }

        $classroom = ArrayToolkit::parts($classroom, self::CLASSROOM_ALLOWED_KEY);

        return $classroom;
    }

    /**
     * @return CourseInfoBuilder
     */
    protected function getCourseDetailBuilder()
    {
        return new CourseInfoBuilder($this->biz);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

}