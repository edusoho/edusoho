<?php


namespace MarketingMallBundle\Common;


use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AbstractException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Strategy\CourseStrategy;
use Codeages\Biz\Framework\Context\Biz;

class CourseDetailBuilder
{
    private $biz;

    const COURSE_ALLOWED_KEY = ['course_ids', 'title', 'sub_title', 'cover', 'summary', 'course_catalogue', 'teacher_list'];

    const TASKS_ALLOWED_KEY = ['title', 'number', 'counts', 'children'];

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function build($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE);
        }
        return $this->buildCourseData($courseId);
    }

    protected function buildCourseData(&$course)
    {
        $classroomCourses = [];
        if ($course['parentId'] == 0){
            $courseSet = $this->getCourseSetService()->getCourseSet($course['id']);
            $classroomCourses = $this->getClassroomService()->findClassroomCourseByCourseSetIds([$courseSet['id']]);
        }
        $tasksList = $this->createCourseStrategy($course)->getTasksListJsonData($course['id'])['data']['items'];
        $teachers = $this->getCourseService()->findTeachersByCourseId($course['id']);
        $tasksList = ArrayToolkit::parts($tasksList, self::TASKS_ALLOWED_KEY);

        $course['course_ids'] = array_merge($course['id'], ArrayToolkit::column($classroomCourses, 'courseId'));
        $course['course_catalogue'] = $tasksList;
        $course['teacher_list'] = $teachers;

        $course = ArrayToolkit::parts($tasksList, self::COURSE_ALLOWED_KEY);

        return $course;
    }


    protected function createNewException($e)
    {
        if ($e instanceof AbstractException) {
            throw $e;
        }

        throw new \Exception();
    }

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    protected function createCourseStrategy($course)
    {
        return $this->biz->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}