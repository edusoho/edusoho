<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\LearningDataAnalysisService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Thread\Service\ThreadService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class ClassroomController extends BaseController
{
    public function teachingAction(Request $request, $tab = 'publish')
    {
        $user = $this->getCurrentUser();
        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = $this->buildTeachingClassroomConditions($tab);

        if (empty($conditions['classroomIds'])) {
            return $this->render('my/teaching/classroom.html.twig', [
                'classrooms' => [],
                'members' => [],
                'paginator' => [],
                'tab' => $tab,
            ]);
        }
        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->countClassrooms($conditions),
            20
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($classrooms as $key => $classroom) {
            $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
            $coursesCount = count($courses);
            $classrooms[$key]['coursesCount'] = $coursesCount;
            $classrooms[$key]['canManageClassroom'] = $this->getClassroomService()->canManageClassroom($classroom['id']);
        }

        return $this->render('my/teaching/classroom.html.twig', [
            'classrooms' => $classrooms,
            'paginator' => $paginator,
            'tab' => $tab,
        ]);
    }

    protected function buildTeachingClassroomConditions($tab)
    {
        $classroomMembers = $this->getClassroomService()->searchMembers(
            ['role' => 'teacher', 'userId' => $this->getCurrentUser()->getId()],
            [],
            0,
            PHP_INT_MAX
        );
        $classroomMembers = array_merge(
            $classroomMembers,
            $this->getClassroomService()->searchMembers(
                ['role' => 'assistant', 'userId' => $this->getCurrentUser()->getId()],
                [],
                0,
                PHP_INT_MAX
            )
        );
        $classroomIds = ArrayToolkit::column($classroomMembers, 'classroomId');

        $status = ['publish' => 'published', 'unPublish' => 'draft', 'closed' => 'closed'];

        return ['classroomIds' => $classroomIds, 'status' => $status[$tab]];
    }

    public function classroomAction()
    {
        $user = $this->getUser();

        $members = $this->getClassroomService()->searchMembers([
            'roles' => ['student', 'auditor'],
            'userId' => $user->id,
        ], ['createdTime' => 'desc'], 0, PHP_INT_MAX);

        $assistants = $this->getClassroomService()->searchMembers([
            'role' => 'assistant',
            'userId' => $user->id,
        ], null, 0, PHP_INT_MAX);

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
            $classrooms[$key]['lastLearnTime'] = $members[$classroom['id']]['createdTime'];
        }
        array_multisort(ArrayToolkit::column($classrooms, 'lastLearnTime'), SORT_DESC, $classrooms);

        return $this->render('my/learning/classroom/classroom.html.twig', [
            'classrooms' => $classrooms,
            'members' => $members,
        ]);
    }

    public function classroomDiscussionsAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = [
            'userId' => $user['id'],
            'type' => 'discussion',
            'targetType' => 'classroom',
        ];

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

        return $this->render('my/learning/classroom/discussions.html.twig', [
            'threadType' => 'classroom',
            'paginator' => $paginator,
            'threads' => $threads,
            'users' => $users,
            'classrooms' => $classrooms,
        ]);
    }

    public function classroomQuestionsAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = [
            'userId' => $user['id'],
            'type' => 'question',
            'targetType' => 'classroom',
        ];

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

        return $this->render('my/learning/classroom/questions.html.twig', [
            'threadType' => 'classroom',
            'paginator' => $paginator,
            'threads' => $threads,
            'users' => $users,
            'classrooms' => $classrooms,
        ]);
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
