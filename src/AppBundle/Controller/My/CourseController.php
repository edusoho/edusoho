<?php


namespace AppBundle\Controller\My;


use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use AppBundle\Controller\CourseBaseController;
use AppBundle\Controller\Course\CourseShowMetas;

class CourseController extends CourseBaseController
{
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


    public function headerForMemberAction(Request $request, $id)
    {

        list($courseSet, $course, $member) = $this->buildCourseLayoutData($request, $id);

        $courses = $this->getCourseService()->findPublishedCoursesByCourseSetId($course['courseSetId']);

        $taskCount = $this->getTaskService()->countTasksByCourseId($id);

        $progress = $taskResultCount = $toLearnTasks = $taskPerDay = $planStudyTaskCount = $planProgressProgress = 0;

        $user = $this->getUser();
        if ($member && $taskCount) {

            //学习记录
            $taskResultCount = $this->getTaskResultService()->countTaskResult(array('courseId' => $id, 'status' => 'finish', 'userId' => $user['id']));

            //学习进度
            $progress = empty($taskCount) ? 0 : round($taskResultCount / $taskCount, 2) * 100;

            //待学习任务
            $toLearnTasks = $this->getTaskService()->findToLearnTasksByCourseId($id);


            //任务式课程每日建议学习任务数
            $taskPerDay = $this->getFinishedTaskPerDay($course, $taskCount);


            //计划应学数量
            $planStudyTaskCount = $this->getPlanStudyTaskCount($course, $member, $taskCount, $taskPerDay);

            //计划进度
            $planProgressProgress = empty($taskCount) ? 0 : round($planStudyTaskCount / $taskCount, 2) * 100;

            //TODO预览的任务
            $previewTaks = $this->getTaskService()->search(array('courseId' => $id, 'isFree' => '1'), array('seq' => 'ASC'), 0, 1);
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

    public function showAction($id, $tab = 'tasks')
    {
        $metas = CourseShowMetas::getMemberCourseShowMetas();
        $currentTab = $metas['tabs'][$tab];

        return $this->render('course/course-show.html.twig', array(
            'metas'      => $metas,
            'currentTab' => $currentTab,
            'forMember'  => true
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