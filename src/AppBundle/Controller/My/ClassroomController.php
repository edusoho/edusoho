<?php

namespace AppBundle\Controller\My;

use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\LearningDataAnalysisService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Thread\Service\ThreadService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;

class ClassroomController extends BaseController
{
    public function teachingAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $orderBy = array('createdTime' => 'desc');

        $classroomMembers = $this->getClassroomService()->searchMembers(array('role' => 'teacher', 'userId' => $user->getId()), $orderBy, 0, PHP_INT_MAX);
        $classroomMembers = array_merge($classroomMembers, $this->getClassroomService()->searchMembers(array('role' => 'assistant', 'userId' => $user->getId()), $orderBy, 0, PHP_INT_MAX));
        $classroomIds = ArrayToolkit::column($classroomMembers, 'classroomId');

        $conditions = array('classroomIds' => $classroomIds);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->countClassrooms($conditions),
            20
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $members = $this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomIds);

        foreach ($classrooms as $key => $classroom) {
            $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
            $courseIds = ArrayToolkit::column($courses, 'id');
            $coursesCount = count($courses);

            $classrooms[$key]['coursesCount'] = $coursesCount;

            $studentCount = $this->getClassroomService()->searchMemberCount(array('role' => 'student', 'classroomId' => $classroom['id'], 'startTimeGreaterThan' => strtotime(date('Y-m-d'))));
            $auditorCount = $this->getClassroomService()->searchMemberCount(array('role' => 'auditor', 'classroomId' => $classroom['id'], 'startTimeGreaterThan' => strtotime(date('Y-m-d'))));

            $allCount = $studentCount + $auditorCount;

            $classrooms[$key]['allCount'] = $allCount;

            $todayTimeStart = strtotime(date('Y-m-d', time()));
            $todayTimeEnd = strtotime(date('Y-m-d', time() + 24 * 3600));

            $todayFinishedTaskNum = $this->getTaskResultService()->countTaskResults(array('courseIds' => (!empty($courseIds)) ? $courseIds : array(-1), 'createdTime_GE' => $todayTimeStart, 'status' => 'finish'));
            $threadCount = $this->getThreadService()->searchThreadCount(array('targetType' => 'classroom', 'targetId' => $classroom['id'], 'type' => 'discussion', 'startTime' => $todayTimeStart, 'endTime' => $todayTimeEnd, 'status' => 'open'));
            $classrooms[$key]['threadCount'] = $threadCount;

            $classrooms[$key]['todayFinishedTaskNum'] = $todayFinishedTaskNum;
        }

        return $this->render('my/teaching/classroom.html.twig', array(
            'classrooms' => $classrooms,
            'members' => $members,
            'paginator' => $paginator,
        ));
    }

    public function classroomAction()
    {
        $user = $this->getUser();

        $members = $this->getClassroomService()->searchMembers(array(
            'roles' => array('student', 'auditor'),
            'userId' => $user->id,
        ), array('createdTime' => 'desc'), 0, PHP_INT_MAX);

        $assistants = $this->getClassroomService()->searchMembers(array(
            'role' => 'assistant',
            'userId' => $user->id,
        ), null, 0, PHP_INT_MAX);

        $members = array_merge($members, $assistants);
        $members = ArrayToolkit::index($members, 'classroomId');

        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        foreach ($classrooms as $key => $classroom) {
            $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
            $coursesCount = count($courses);

            $classrooms[$key]['coursesCount'] = $coursesCount;

            $time = time() - $members[$classroom['id']]['createdTime'];
            $day = intval($time / (3600 * 24));

            $classrooms[$key]['day'] = $day;

            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($classroom['id'], $user['id']);
            $classrooms[$key]['learningProgressPercent'] = $progress['percent'];
        }

        return $this->render('my/learning/classroom/classroom.html.twig', array(
            'classrooms' => $classrooms,
            'members' => $members,
        ));
    }

    public function classroomDiscussionsAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = array(
            'userId' => $user['id'],
            'type' => 'discussion',
            'targetType' => 'classroom',
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'lastPostUserId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($threads, 'targetId'));

        return $this->render('my/learning/classroom/discussions.html.twig', array(
            'threadType' => 'classroom',
            'paginator' => $paginator,
            'threads' => $threads,
            'users' => $users,
            'classrooms' => $classrooms,
        ));
    }

    public function classroomQuestionsAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = array(
            'userId' => $user['id'],
            'type' => 'question',
            'targetType' => 'classroom',
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'lastPostUserId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($threads, 'targetId'));

        return $this->render('my/learning/classroom/questions.html.twig', array(
            'threadType' => 'classroom',
            'paginator' => $paginator,
            'threads' => $threads,
            'users' => $users,
            'classrooms' => $classrooms,
        ));
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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

    /**
     * @return LearningDataAnalysisService
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->createService('Classroom:LearningDataAnalysisService');
    }
}
