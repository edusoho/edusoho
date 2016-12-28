<?php

namespace AppBundle\Controller;

use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
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

    public function headerAction(Request $request, $id)
    {
        list($courseSet, $course, $member) = $this->buildCourseLayoutData($request, $id);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($course['courseSetId']);

        $taskCount       = $this->getTaskService()->countTasksByCourseId($id);
        $taskResultCount = $this->getTaskResultService()->countTaskResult(array('courseId' => $id, 'status' => 'finish'));

        $progress = round($taskResultCount / $taskCount, 2); //学习进度
        //学习进度
        //下一个课时
        return $this->render('course-set/header.html.twig', array(
            'courseSet'        => $courseSet,
            'courses'          => $courses,
            'course'           => $course,
            'member'           => $member,
            'progress'         => $progress,
            'taskCount'       => $taskCount,
            'taskResultCount' => $taskResultCount
        ));
    }

    public function notesAction($id)
    {
        list($courseSet, $course) = $this->tryGetCourseSetAndCourse($id);

        $notes = $this->getCourseNoteService()->findPublicNotesByCourseId($course['id']);

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

    public function reviewListAction(Request $request, $id)
    {
        list($courseSet, $course) = $this->tryGetCourseSetAndCourse($id);
        list($course, $member) = $this->getCourseService()->tryTakeCourse($course['id']);

        $courseId = $request->query->get('courseId', 0);

        $conditions = array(
            'courseSetId' => $courseSet['id'],
            'parentId'    => 0
        );

        if ($courseId > 0) {
            $conditions['courseId'] = $courseId;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getReviewService()->searchReviewsCount($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $user       = $this->getCurrentUser();
        $userReview = $this->getReviewService()->getUserCourseReview($user['id'], $course['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render('course-set/review/list.html.twig', array(
            'courseSet'  => $courseSet,
            'course'     => $course,
            'reviews'    => $reviews,
            'userReview' => $userReview,
            'users'      => $users,
            'member'     => $member
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
            'courses' => $courses,
            'users'   => $users,
            //'classroomIds' => $classroomIds,
            'mode'    => $mode
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

    public function characteristicPartAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        $tasks = $this->getTaskService()->findTasksFetchActivityByCourseId($course['id']);

        $characteristicData = array();

        foreach ($tasks as $task) {
            $type = strtolower($task['activity']['mediaType']);
            isset($characteristicData[$type]) ? $characteristicData[$type]++ : $characteristicData[$type] = 1;
        }

        return $this->render('course/part/characteristic.html.twig', array(
            'course'             => $course,
            'characteristicData' => $characteristicData
        ));
    }

    public function otherCoursePartAction(Request $request, $id)
    {
        list($courseSet, $course) = $this->tryGetCourseSetAndCourse($id);

        $otherCourse = $course; // $this->getCourseService()->getOtherCourses($course['id']);

        return $this->render('course/part/other-course.html.twig', array(
            'otherCourse' => $otherCourse,
            'courseSet'   => $courseSet
        ));
    }

    public function teachersPartAction(Request $request, $id)
    {
        list(, $course) = $this->tryGetCourseSetAndCourse($id);

        $teachers = $this->getUserService()->findUsersByIds($course['teacherIds']);

        return $this->render('course/part/teachers.html.twig', array(
            'teachers' => $teachers
        ));
    }

    public function newestStudentsPartAction(Request $request, $id)
    {
        list(, $course) = $this->tryGetCourseSetAndCourse($id);

        $conditions = array(
            'courseId' => $course['id'],
            'role'     => 'student',
            'locked'   => 0
        );

        $members    = $this->getMemberService()->searchMembers($conditions, array('createdTime' => 'DESC'), 0, 20);
        $studentIds = ArrayToolkit::column($members, 'userId');
        $students   = $this->getUserService()->findUsersByIds($studentIds);

        return $this->render('course/part/newest-students.html.twig', array(
            'students' => $students
        ));
    }

    // TODO old
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
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

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }
}
