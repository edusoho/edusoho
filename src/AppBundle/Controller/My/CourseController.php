<?php

namespace AppBundle\Controller\My;

use Biz\Task\Service\TaskService;
use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Course\CourseBaseController;

class CourseController extends CourseBaseController
{
    public function indexAction(Request $request)
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
        $paginator   = new Paginator(
            $request,
            $this->getCourseService()->findUserLeaningCourseCount($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findUserLeaningCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('my/course/learning.html.twig', array(
            'courses'   => $courses,
            'paginator' => $paginator
        ));
    }

    public function learnedAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator   = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserLeanedCourseCount($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findUserLeanedCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array();
        foreach ($courses as $key => $course) {
            $userIds   = array_merge($userIds, $course['teacherIds']);
            $learnTime = $this->getCourseService()->searchLearnTime(array('courseId' => $course['id'], 'userId' => $currentUser['id']));

            $courses[$key]['learnTime'] = intval($learnTime / 60 / 60).$this->trans('小时').($learnTime / 60 % 60).$this->trans('分钟');
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('my/course/learned.html.twig', array(
            'courses'   => $courses,
            'users'     => $users,
            'paginator' => $paginator
        ));
    }

    public function favoritedAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $conditions = array(
            'userId' => $currentUser['id']
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseFavoriteCount($conditions),
            12
        );

        $courseFavorites = $this->getCourseService()->searchCourseFavorites(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('my/course/favorited.html.twig', array(
            'courseFavorites' => $courseFavorites,
            'paginator'       => $paginator
        ));
    }



    public function headerForMemberAction(Request $request, $course, $member)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $courses   = $this->getCourseService()->findPublishedCoursesByCourseSetId($course['courseSetId']);
        $taskCount = $this->getTaskService()->countTasksByCourseId($course['id']);
        $progress  = $taskResultCount  = $toLearnTasks  = $taskPerDay  = $planStudyTaskCount  = $planProgressProgress  = 0;

        $user = $this->getUser();
        if ($taskCount) {
            //学习记录
            $taskResultCount = $this->getTaskResultService()->countTaskResult(array('courseId' => $course['id'], 'status' => 'finish', 'userId' => $user['id']));

            //学习进度
            $progress = empty($taskCount) ? 0 : round($taskResultCount / $taskCount, 2) * 100;

            //待学习任务
            $toLearnTasks = $this->getTaskService()->findToLearnTasksByCourseId($course['id']);

            //任务式课程每日建议学习任务数
            $taskPerDay = $this->getFinishedTaskPerDay($course, $taskCount);

            //计划应学数量
            $planStudyTaskCount = $this->getPlanStudyTaskCount($course, $member, $taskCount, $taskPerDay);

            //计划进度
            $planProgressProgress = empty($taskCount) ? 0 : round($planStudyTaskCount / $taskCount, 2) * 100;

            //TODO预览的任务
            $previewTaks = $this->getTaskService()->search(array('courseId' => $course['id'], 'isFree' => '1'), array('seq' => 'ASC'), 0, 1);
        }

        $isUserFavorite = false;
        if ($user->isLogin()) {
            $isUserFavorite = $this->getCourseSetService()->isUserFavorite($user['id'], $course['courseSetId']);
        }

        return $this->render('course/header/header-for-member.html.twig', array(
            'courseSet'            => $courseSet,
            'courses'              => $courses,
            'course'               => $course,
            'member'               => $member,
            'progress'             => $progress,
            'taskCount'            => $taskCount,
            'taskResultCount'      => $taskResultCount,
            'toLearnTasks'         => $toLearnTasks,
            'taskPerDay'           => $taskPerDay,
            'planStudyTaskCount'   => $planStudyTaskCount,
            'planProgressProgress' => $planProgressProgress,
            'isUserFavorite'       => $isUserFavorite
        ));
    }

    public function showAction(Request $request, $id, $tab = 'tasks')
    {
        $course = $this->getCourseService()->getCourse($id);
        $member = $this->getCourseMember($request, $course);

        if (empty($member)) {
            return $this->redirect($this->generateUrl('course_show', array(
                'id'  => $id,
                'tab' => $tab
            )));
        }

        return $this->render('course/course-show.html.twig', array(
            'tab'    => $tab,
            'member' => $member
        ));
    }

    public function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getFinishedTaskPerDay($course, $taskNum)
    {
        //自由式不需要展示每日计划的学习任务数
        if ($course['learnMode'] == 'freeMode') {
            return false;
        }
        if ($course['expiryMode'] == 'days') {
            $finishedTaskPerDay = empty($course['expiryDays']) ? false : $taskNum / $course['expiryDays'];
        } else {
            $diffDay            = ($course['expiryEndDate'] - $course['expiryStartDate']) / (24 * 60 * 60);
            $finishedTaskPerDay = empty($diffDay) ? false : $taskNum / $diffDay;
        }
        return round($finishedTaskPerDay);
    }

    protected function getPlanStudyTaskCount($course, $member, $taskNum, $taskPerDay)
    {
        //自由式不需要展示应学任务数, 未设置学习有效期不需要展示应学任务数
        if ($course['learnMode'] == 'freeMode' || empty($taskPerDay)) {
            return false;
        }
        //当前时间减去课程
        //按天计算有效期， 当前的时间- 加入课程的时间 获得天数* 每天应学任务
        if ($course['expiryMode'] == 'days') {
            $joinDays = (time() - $member['createdTime']) / (24 * 60 * 60);
        } else {
            //当前时间-减去课程有效期开始时间  获得天数 *应学任务数量
            $joinDays = (time() - $course['expiryStartDate']) / (24 * 60 * 60);
        }

        return $taskPerDay * $joinDays >= $taskNum ? $taskNum : round($taskPerDay * $joinDays);
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
}
