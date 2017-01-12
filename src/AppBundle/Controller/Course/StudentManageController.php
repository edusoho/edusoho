<?php

namespace AppBundle\Controller\Course;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Topxia\Common\SimpleValidator;
use Biz\Order\Service\OrderService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserFieldService;
use Biz\Task\Service\TaskResultService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\ActivityLearnLogService;

class StudentManageController extends BaseController
{
    public function studentsAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $students  = $this->getCourseService()->findStudentsByCourseId($courseId);
        //TODO find students的学习进度（已完成任务数/总任务数）
        $processes = array();
        if (!empty($students)) {
            $taskCount = $this->getTaskService()->countTasksByCourseId($courseId);
            foreach ($students as $student) {
                $processes[$student['userId']] = $this->calcStudentLearnProcess($student['userId'], $courseId, $taskCount);
            }
        }
        return $this->render('course-manage/student/index.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course,
            'students'  => $students,
            'processes' => $processes
        ));
    }

    public function studentQuitRecordsAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $fields    = $request->query->all();
        $condition = array();

        if (isset($fields['keyword']) && !empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserIds($fields['keyword']);
        }

        $condition['targetId']   = $courseId;
        $condition['targetType'] = 'course';
        $condition['status']     = 'success';

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countRefunds($condition),
            20
        );

        $refunds = $this->getOrderService()->searchRefunds(
            $condition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($refunds as $key => $refund) {
            $refunds[$key]['user'] = $this->getUserService()->getUser($refund['userId']);

            $refunds[$key]['order'] = $this->getOrderService()->getOrder($refund['orderId']);
        }

        return $this->render('course-manage/student/quit-records.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course,
            'refunds'   => $refunds,
            'paginator' => $paginator,
            'role'      => 'student'
        ));
    }

    public function createCourseStudentAction(Request $request, $courseSetId, $courseId)
    {
        if ($request->isMethod('POST')) {
            $data           = $request->request->all();
            $user           = $this->getUserService()->getUserByLoginField($data['queryfield']);
            $data['userId'] = $user['id'];
            $this->getCourseMemberService()->becomeStudentAndCreateOrder($user['id'], $courseId, $data);
            return $this->redirect($this->generateUrl('course_set_manage_course_students', array('courseSetId' => $courseSetId, 'courseId' => $courseId)));
        }
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->render('course-manage/student/add-modal.html.twig', array(
            'course'      => $course,
            'courseSetId' => $courseSetId
        ));
    }

    public function removeCourseStudentAction(Request $request, $courseSetId, $courseId, $userId)
    {
        $this->getCourseMemberService()->removeCourseStudent($courseId, $userId);
        return $this->createJsonResponse(array('success' => true));
    }

    public function checkStudentAction(Request $request, $courseSetId, $courseId)
    {
        $keyword = $request->query->get('value');
        $user    = $this->getUserService()->getUserByLoginField($keyword);

        $response = true;
        if (!$user) {
            $response = '该用户不存在';
        } else {
            $isCourseStudent = $this->getCourseMemberService()->isCourseStudent($courseId, $user['id']);

            if ($isCourseStudent) {
                $response = '该用户已是本课程的学员了';
            } else {
                $isCourseTeacher = $this->getCourseMemberService()->isCourseTeacher($courseId, $user['id']);

                if ($isCourseTeacher) {
                    $response = '该用户是本课程的教师，不能添加';
                }
            }
        }
        return $this->createJsonResponse($response);
    }

    public function showAction(Request $request, $courseId, $userId)
    {
        if (!$this->getCurrentUser()->isAdmin()) {
            throw $this->createAccessDeniedException('您无权查看学员详细信息！');
        }

        $user             = $this->getUserService()->getUser($userId);
        $profile          = $this->getUserService()->getUserProfile($userId);
        $profile['title'] = $user['title'];

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        for ($i = 0; $i < count($userFields); $i++) {
            if (strstr($userFields[$i]['fieldName'], "textField")) {
                $userFields[$i]['type'] = "text";
            }

            if (strstr($userFields[$i]['fieldName'], "varcharField")) {
                $userFields[$i]['type'] = "varchar";
            }

            if (strstr($userFields[$i]['fieldName'], "intField")) {
                $userFields[$i]['type'] = "int";
            }

            if (strstr($userFields[$i]['fieldName'], "floatField")) {
                $userFields[$i]['type'] = "float";
            }

            if (strstr($userFields[$i]['fieldName'], "dateField")) {
                $userFields[$i]['type'] = "date";
            }
        }

        return $this->render('course-manage/student/show-modal.html.twig', array(
            'user'       => $user,
            'profile'    => $profile,
            'userFields' => $userFields
        ));
    }

    public function studyProcessAction(Request $request, $courseSetId, $courseId, $userId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $student = $this->getCourseMemberService()->getCourseMember($courseId, $userId);
        if (empty($student)) {
            throw $this->createNotFoundException('Student#{$userId} Not Found');
        }
        $user = $this->getUserService()->getUser($student['userId']);

        $questionCount   = $this->getCourseMemberService()->countQuestionsByCourseIdAndUserId($courseId, $userId);
        $activityCount   = $this->getCourseMemberService()->countActivitiesByCourseIdAndUserId($courseId, $userId);
        $discussionCount = $this->getCourseMemberService()->countDiscussionsByCourseIdAndUserId($courseId, $userId);
        $postCount       = $this->getCourseMemberService()->countPostsByCourseIdAndUserId($courseId, $userId);

        list($daysCount, $learnedTime, $learnedTimePerDay) = $this->getActivityLearnLogService()->calcLearnProcessByCourseIdAndUserId($courseId, $userId);

        return $this->render('course-manage/student/process-modal.html.twig', array(
            'course'            => $course,
            'student'           => $student,
            'user'              => $user,
            'questionCount'     => $questionCount,
            'activityCount'     => $activityCount,
            'discussionCount'   => $discussionCount,
            'postCount'         => $postCount,
            'daysCount'         => $daysCount,
            'learnedTime'       => round($learnedTime / 60, 2),
            'learnedTimePerDay' => round($learnedTimePerDay / 60, 2)
        ));
    }

    public function reportCardAction(Request $request, $course, $user)
    {
        $reportCard = $this->createReportCard($course, $user);

        return $this->render('course-manage/student/report-card.html.twig', $reportCard);
    }

    private function calcStudentLearnProcess($userId, $courseId, $taskCount)
    {
        $learnedCount = $this->getTaskResultService()->countUserLearnedTasksByCourseId($courseId, $userId);
        return $taskCount <= 0 ? '0' : sprintf('%d', $learnedCount / $taskCount * 100.0);
    }

    private function createReportCard($course, $user)
    {
        $reportCard = array();

        //homeworks&testpapers合并处理，定义为：test(type=[homework,testpaper])
        $activities              = array();
        $allTests                = array();
        $finishedTests           = array();
        $reviewingTests          = array();
        $bestTests               = array();
        $homeworksCount          = 0;
        $testpapersCount         = 0;
        $finishedHomeworksCount  = 0;
        $finishedTestpapersCount = 0;

        $tasks = $this->getTaskService()->findTasksByCourseId($course['id']);

        if (empty($tasks)) {
            goto result;
        }
        $activitiyIds       = ArrayToolkit::column($tasks, 'activityId');
        $activitiesWithMeta = $this->getActivityService()->findActivities($activitiyIds, true);

        foreach ($activitiesWithMeta as $activity) {
            if ($activity['mediaType'] == 'homework') {
                $homeworksCount += 1;
                $activities[] = array('activityId' => $activity['id'], 'mediaId' => $activity['mediaId']);
            } elseif ($activity['mediaType'] == 'testpaper') {
                $testpapersCount += 1;
                $activities[] = array('activityId' => $activity['id'], 'mediaId' => $activity['ext']['mediaId']);
            }
        }

        $finishedTargets  = array();
        $reviewingTargets = array();
        if (!empty($activities)) {
            $testIds = ArrayToolkit::column($activities, 'mediaId');

            $allTests = $this->getTestpaperService()->findTestpapersByIds($testIds);

            $finishedTargets = $this->getTestpaperService()->searchTestpaperResults(array(
                'testIds' => $testIds,
                'userId'  => $user['id'],
                'status'  => 'finished',
                'types'   => array('homework', 'testpaper')
            ), array('testId' => 'ASC', 'beginTime' => 'ASC'), 0, PHP_INT_MAX);

            $reviewingTargets = $this->getTestpaperService()->searchTestpaperResults(array(
                'testIds' => $testIds,
                'userId'  => $user['id'],
                'status'  => 'reviewing',
                'types'   => array('homework', 'testpaper')
            ), array('testId' => 'ASC', 'beginTime' => 'ASC'), 0, PHP_INT_MAX);
        }

        if (!empty($finishedTargets)) {
            $currentTestId = 0;
            foreach ($finishedTargets as $target) {
                if ($currentTestId == 0 || $currentTestId != $target['testId']) {
                    $currentTestId = $target['testId'];

                    if ($target['type'] == 'homework') {
                        $finishedHomeworksCount += 1;
                    } else {
                        $finishedTestpapersCount += 1;
                    }
                }

                if (empty($bestTests[$currentTestId])) {
                    $bestTests[$currentTestId] = array();
                }
                if ($this->gradeBetterThan($target, $bestTests[$currentTestId])) {
                    $bestTests[$currentTestId] = $target;
                }

                if (empty($finishedTests[$currentTestId])) {
                    $finishedTests[$currentTestId] = array();
                }
                $finishedTests[$currentTestId][] = $target;
            }
        }

        if (!empty($reviewingTargets)) {
            $currentTestId = 0;
            foreach ($reviewingTargets as $target) {
                if ($currentTestId == 0 || $currentTestId != $target['testId']) {
                    $currentTestId = $target['testId'];
                }
                if (empty($reviewingTests[$currentTestId])) {
                    $reviewingTests[$currentTestId] = array();
                }
                $reviewingTests[$currentTestId][] = $target;
            }
        }

        goto result;

        result:
        $reportCard['activities']     = $activities;
        $reportCard['allTests']       = ArrayToolkit::index($allTests, 'id');
        $reportCard['finishedTests']  = $finishedTests;
        $reportCard['reviewingTests'] = $reviewingTests;
        $reportCard['bestTests']      = $bestTests;

        $reportCard['homeworksCount']          = $homeworksCount;
        $reportCard['testpapersCount']         = $testpapersCount;
        $reportCard['finishedHomeworksCount']  = $finishedHomeworksCount;
        $reportCard['finishedTestpapersCount'] = $finishedTestpapersCount;

        return $reportCard;
    }

    private function gradeBetterThan($source, $target)
    {
        if (empty($target)) {
            return true;
        }

        $levels      = array('excellent', 'good', 'passed', 'unpassed', 'none');
        $levels      = array_values($levels);
        $sourceIndex = array_search($source['passedStatus'], $levels);
        $targetIndex = array_search($target['passedStatus'], $levels);

        if ($sourceIndex < $targetIndex) {
            return true;
        } elseif ($sourceIndex < $targetIndex) {
            return $source['score'] >= $target['score'];
        } else {
            return false;
        }
    }

    private function getUserIds($keyword)
    {
        $userIds = array();

        if (SimpleValidator::email($keyword)) {
            $user = $this->getUserService()->getUserByEmail($keyword);

            $userIds[] = $user ? $user['id'] : null;
            return $userIds;
        } elseif (SimpleValidator::mobile($keyword)) {
            $mobileVerifiedUser = $this->getUserService()->getUserByVerifiedMobile($keyword);
            $profileUsers       = $this->getUserService()->searchUserProfiles(array('tel' => $keyword), array('id', 'DESC'), 0, PHP_INT_MAX);
            $mobileNameUser     = $this->getUserService()->getUserByNickname($keyword);
            $userIds            = $profileUsers ? ArrayToolkit::column($profileUsers, 'id') : null;

            $userIds[] = $mobileVerifiedUser ? $mobileVerifiedUser['id'] : null;
            $userIds[] = $mobileNameUser ? $mobileNameUser['id'] : null;

            $userIds = array_unique($userIds);

            $userIds = $userIds ? $userIds : null;
            return $userIds;
        } else {
            $user      = $this->getUserService()->getUserByNickname($keyword);
            $userIds[] = $user ? $user['id'] : null;
            return $userIds;
        }
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
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
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
