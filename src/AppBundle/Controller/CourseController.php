<?php

namespace AppBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Note\Service\CourseNoteService;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends CourseBaseController
{
    public function showAction($id)
    {
        list($courseSet, $course) = $this->tryGetCourseSetAndCourse($id);
        $courseItems = $this->getCourseService()->findCourseItems($course['id']);

        return $this->render('course-set/overview.html.twig', array(
            'courseSet'   => $courseSet,
            'course'      => $course,
            'courseItems' => $courseItems
        ));
    }

    public function notesAction($id)
    {
        list($courseSet, $course) = $this->tryGetCourseSetAndCourse($id);

        $notes = $this->getCourseNoteService()->findPublicNotesByCourseSetId($courseSet['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($notes, 'userId'));
        $users = ArrayToolkit::index($users, 'id');

        $tasks = $this->getTaskService()->findTasksByIds(ArrayToolkit::column($notes, 'taskId'));
        $tasks = ArrayToolkit::index($tasks, 'id');

        $currentUser = $this->getCurrentUser();
        $likes       = $this->getCourseNoteService()->findNoteLikesByUserId($currentUser['id']);
        $likeNoteIds = ArrayToolkit::column($likes, 'noteId');
        return $this->render('course-set/note/notes.html.twig', array(
            'course'      => $course,
            'courseSet'   => $courseSet,
            'notes'       => $notes,
            'users'       => $users,
            'tasks'       => $tasks,
            'likeNoteIds' => $likeNoteIds
        ));
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

    public function taskListAction(Request $request, $id)
    {
        list($courseSet, $course) = $this->tryGetCourseSetAndCourse($id);
        $courseItems = $this->getCourseService()->findCourseItems($id);

        return $this->render('course-set/task-list.html.twig', array(
            'course'      => $course,
            'courseSet'   => $courseSet,
            'courseItems' => $courseItems
        ));
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

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->createService('Note:CourseNoteService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
