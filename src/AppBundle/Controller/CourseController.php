<?php


namespace AppBundle\Controller;


use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;


class CourseController extends BaseController
{
    public function showAction($request, $id)
    {
        
    }

    public function coursesBlockAction($courses, $view = 'list', $mode = 'default')
    {
        $userIds = array();

        foreach ($courses as $key => $course) {
            //TODO
            // $userIds = array_merge($userIds, $course['teacherIds']);

            $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($course['id']);

            $courses[$key]['classroomCount'] = count($classroomIds);
            $courses[$key]['courseSet']      = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            if (count($classroomIds) > 0) {
                $classroom                  = $this->getClassroomService()->getClassroom($classroomIds[0]);
                $courses[$key]['classroom'] = $classroom;
            }
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("course/courses-block-{$view}.html.twig", array(
            'courses'      => $courses,
            'users'        => $users,
            'classroomIds' => $classroomIds,
            'mode'         => $mode
        ));
    }


    public function taskListAction(Request $request, $courseId)
    {
        $courseItems = $this->getCourseService()->findCourseItems($courseId);

        var_dump($courseItems);
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    // TODO old
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }


}