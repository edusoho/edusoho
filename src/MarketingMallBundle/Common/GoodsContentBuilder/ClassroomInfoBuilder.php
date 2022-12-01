<?php

namespace MarketingMallBundle\Common\GoodsContentBuilder;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;

class ClassroomInfoBuilder extends AbstractBuilder
{
    const CLASSROOM_ALLOWED_KEY = ['classroomId', 'classroomCatalogue', 'cover', 'title', 'subtitle', 'about', 'price'];

    public function build($id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);
        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM);
        }

        return $this->buildClassroomData($classroom);
    }

    protected function buildClassroomData($classroom, $multiple = false)
    {
        $courseIds = ArrayToolkit::column($this->getClassroomService()->findCoursesByClassroomId($classroom['id']), 'id');

        $classroom['classroomId'] = $classroom['id'];
        $classroom['cover'] = $this->transformCover(['small' => $classroom['smallPicture'], 'middle' => $classroom['middlePicture']], 'classroom.png');
        $classroom['classroomCatalogue'] = [];

        foreach ($courseIds as $courseId) {
            $course = $this->getCourseDetailBuilder()->build($courseId);
            if($multiple == true) {
//                unset($course['teacherList']);
                $course['teacherIds'] = ArrayToolkit::column($this->getCourseService()->findTeachersByCourseId($courseId),'userId');
            }

            unset($course['courseIds']);
            $course['courseId'] = $courseId;
            $classroom['classroomCatalogue'][] = $course;
        }

        $classroom = ArrayToolkit::parts($classroom, self::CLASSROOM_ALLOWED_KEY);
        $classroom['about'] = $this->transformImages($classroom['about']);

        return $classroom;
    }


    public function builds($ids)
    {
        $classrooms = $this->getClassroomService()->findClassroomsByIds($ids);

        if (empty($classrooms)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM);
        }

        return $this->buildClassroomDatas($classrooms);
    }

    protected function buildClassroomDatas($classrooms)
    {
        $goodsContent = [];

        foreach ($classrooms as $classroom) {
            $classroom = $this->buildClassroomData($classroom, $multiple = true);

            array_push($goodsContent,$classroom);
        }

        return $goodsContent;
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
