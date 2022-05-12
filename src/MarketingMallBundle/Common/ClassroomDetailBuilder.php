<?php


namespace MarketingMallBundle\Common;


use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Task\Strategy\CourseStrategy;
use Codeages\Biz\Framework\Context\Biz;

class ClassroomDetailBuilder
{
    private $biz;

    const CLASSROOM_ALLOWED_KEY = ['classroom_id', 'classroom_catalogue'];

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function build($classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }

        return $this->buildClassroomData($classroom);
    }

    protected function buildClassroomData($classroom)
    {
        $courseIds = ArrayToolkit::column($this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']), 'courseId');

        foreach ($courseIds as $courseId) {
            $course = $this->getCourseDetailBuilder()->build($courseId);
            $course['course_id'] = $courseId;
            unset($course['course_ids']);
            $classroom['classroom_catalogue'][] = $course;
        }

        $classroom['classroom_id'] = $classroom['id'];
        $classroom = ArrayToolkit::parts($classroom, self::CLASSROOM_ALLOWED_KEY);

        return $classroom;
    }

    /**
     * @return CourseDetailBuilder
     */
    protected function getCourseDetailBuilder()
    {
        return new CourseDetailBuilder($this->biz);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

}