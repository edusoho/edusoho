<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Course\CourseBaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends CourseBaseController
{
    public function indexAction()
    {
        if ($this->getCurrentUser()->isTeacher()) {
            return $this->redirect($this->generateUrl('my_teaching_course_sets'));
        } else {
            return $this->redirect($this->generateUrl('my_courses_learning'));
        }
    }

    public function learningAction(Request $request)
    {
        $currentUser = $this->getUser();
        $paginator = new Paginator(
            $request,
            $this->getCourseService()->countUserLearningCourses($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findUserLearningCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $setIds = ArrayToolkit::column($courses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($setIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        return $this->render(
            'my/learning/course/learning.html.twig',
            array(
                'courses' => $courses,
                'paginator' => $paginator,
                'courseSets' => $courseSets,
            )
        );
    }

    public function learnedAction()
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->countUserLearnedCourses($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findUserLearnedCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array();

        foreach ($courses as $key => $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
            $learnTime = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId(
                $course['id'],
                $currentUser['id']
            );

            $courses[$key]['learnTime'] = $learnTime;
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'my/learning/course/learned.html.twig',
            array(
                'courses' => $courses,
                'users' => $users,
                'paginator' => $paginator,
            )
        );
    }

    public function headerForMemberAction($course, $member)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $courses = $this->getCourseService()->findPublishedCoursesByCourseSetId($course['courseSetId']);

        $breadcrumbs = $this->getCategoryService()->findCategoryBreadcrumbs($courseSet['categoryId']);

        if (empty($member['previewAs'])) {
            $learnProgress = $this->getCourseService()->getUserLearningProcess($course['id'], $member['userId']);
        } else {
            $learnProgress = array(
                'taskCount' => 0,
                'progress' => 0,
                'taskResultCount' => 0,
                'toLearnTasks' => 0,
                'taskPerDay' => 0,
                'planStudyTaskCount' => 0,
                'planProgressProgress' => 0,
            );
        }

        $isUserFavorite = false;
        $user = $this->getUser();
        if ($user->isLogin()) {
            $isUserFavorite = $this->getCourseSetService()->isUserFavorite($user['id'], $course['courseSetId']);
        }

        return $this->render(
            'course/header/header-for-member.html.twig',
            array(
                'courseSet' => $courseSet,
                'courses' => $courses,
                'course' => $course,
                'member' => $member,
                'taskCount' => $learnProgress['taskCount'],
                'progress' => $learnProgress['progress'],
                'taskResultCount' => $learnProgress['taskResultCount'],
                'toLearnTasks' => $learnProgress['toLearnTasks'],
                'taskPerDay' => $learnProgress['taskPerDay'],
                'planStudyTaskCount' => $learnProgress['planStudyTaskCount'],
                'planProgressProgress' => $learnProgress['planProgressProgress'],
                'isUserFavorite' => $isUserFavorite,
                'marketingPage' => 0,
                'breadcrumbs' => $breadcrumbs,
            )
        );
    }

    public function showAction(Request $request, $id, $tab = 'tasks')
    {
        $course = $this->getCourseService()->getCourse($id);
        $member = $this->getCourseMember($request, $course);

        $classroom = array();
        if ($course['parentId'] > 0) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
        }

        // 访问班级课程时确保将用户添加到课程member中
        if (!empty($classroom) && empty($member)) {
            $this->joinCourseMemberByClassroomId($course['id'], $classroom['id']);
        }

        if (empty($member)) {
            return $this->redirect(
                $this->generateUrl(
                    'course_show',
                    array(
                        'id' => $id,
                        'tab' => $tab,
                    )
                )
            );
        }

        if ($course['expiryMode'] == 'date' && $course['expiryStartDate'] >= time()) {
            return $this->redirectToRoute('course_show', array('id' => $course['id']));
        }

        $tags = $this->findCourseSetTagsByCourseSetId($course['courseSetId']);

        return $this->render(
            'course/course-show.html.twig',
            array(
                'tab' => $tab,
                'tags' => $tags,
                'member' => $member,
                'isCourseTeacher' => $member['role'] == 'teacher',
                'course' => $course,
                'classroom' => $classroom,
            )
        );
    }

    /**
     * 当用户是班级学员却不在课程学员中时，将学员添加到课程学员中.
     *
     * @param $courseId
     * @param $classroomId
     */
    protected function joinCourseMemberByClassroomId($courseId, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $user = $this->getCurrentUser();

        $classroomMember = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);

        if (empty($classroomMember) || !in_array('student', $classroomMember['role'])) {
            return;
        }

        $info = array(
            'levelId' => empty($classroomMember['levelId']) ? 0 : $classroomMember['levelId'],
            'deadline' => $classroomMember['deadline'],
        );

        $this->getMemberService()->createMemberByClassroomJoined($courseId, $user['id'], $classroom['id'], $info);
    }

    /**
     * @return TaskResultService
     */
    public function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CategoryService
     */
    private function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
