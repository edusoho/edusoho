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
        return $this->render('course-manage/student/index.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course,
            'students'  => $students
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

    public function studyProcessAction(Request $request, $courseSetId, $courseId, $userId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        //FIXME getCourseMember ：用户可能在courseId下既是学员又是老师
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

    private function createReportCard($course, $user)
    {
        $reportCard = array();

        //TODO:
        //1. 查询出所有homeworks、testpapers
        //2. 根据每个homework、testpaper查询出当前用户的result（testpaper_result: status=finish）
        //3. 根据每个homework、testpaper查询出当前用户的result（testpaper_result: status=reviewing）
        //4. 计算出每个homework、testpaper的bestResult（status=finish & max(score)）

        $tasks               = array();
        $allHomeworks        = array();
        $finishedHomeworks   = array();
        $reviewingHomeworks  = array();
        $bestHomeworks       = array();
        $allTestpapers       = array();
        $finishedTestpapers  = array();
        $reviewingTestpapers = array();
        $bestTestpapers      = array();

        $tasks = $this->getTaskService()->findTasksByCourseId($course['id']);

        if (empty($tasks)) {
            goto result;
        }
        $activitiyIds = ArrayToolkit::column($tasks, 'activityId');
        $activities   = $this->getActivityService()->findActivities($activitiyIds, true);

        $targetIds = array();
        foreach ($activities as $activity) {
            if ($activity['mediaType'] == 'homework') {
                $targetIds[] = $activity['mediaId'];
            } elseif ($activity['mediaType'] == 'testpaper') {
                $targetIds[] = $activity['ext']['mediaId'];
            }
        }

        //homeworks&testpapers合并查询
        $allTargets       = array();
        $finishedTargets  = array();
        $reviewingTargets = array();
        if (!empty($targetIds)) {
            $allTargets = $this->getTestpaperService()->findTestpapersByIds($targetIds);

            $finishedTargets = $this->getTestpaperService()->searchTestpaperResults(array(
                'testIds' => $targetIds,
                'userId'  => $user['id'],
                'status'  => 'finished',
                'types'   => array('homework', 'testpaper')
            ), array('testId' => 'ASC', 'beginTime' => 'ASC'), 0, PHP_INT_MAX);

            $reviewingTargets = $this->getTestpaperService()->searchTestpaperResults(array(
                'testIds' => $targetIds,
                'userId'  => $user['id'],
                'status'  => 'reviewing',
                'types'   => array('homework', 'testpaper')
            ), array('testId' => 'ASC', 'beginTime' => 'ASC'), 0, PHP_INT_MAX);
        }

        if (!empty($allTargets)) {
            foreach ($allTargets as $target) {
                if ($target['type'] == 'homework') {
                    $allHomeworks[] = $target;
                } else {
                    $allTestpapers[] = $target;
                }
            }
        }

        if (!empty($finishedTargets)) {
            $currentTestId = 0;
            foreach ($finishedTargets as $target) {
                if ($currentTestId == 0 || $currentTestId != $target['testId']) {
                    $currentTestId = $target['testId'];
                    if ($target['type'] == 'homework') {
                        if (empty($bestHomeworks[$currentTestId])) {
                            $bestHomeworks[$currentTestId] = array();
                        }
                        if ($this->gradeBetterThan($target, $bestHomeworks[$currentTestId])) {
                            $bestHomeworks[$currentTestId] = $target;
                        }
                    } else {
                        if (empty($bestTestpapers[$currentTestId])) {
                            $bestTestpapers[$currentTestId] = array();
                        }
                        if ($this->gradeBetterThan($target, $bestTestpapers[$currentTestId])) {
                            $bestTestpapers[$currentTestId] = $target;
                        }
                    }
                }

                if ($target['type'] == 'homework') {
                    if (empty($finishedHomeworks[$currentTestId])) {
                        $finishedHomeworks[$currentTestId] = array();
                    }
                    $finishedHomeworks[$currentTestId][] = $target;
                } else {
                    if (empty($finishedTestpapers[$currentTestId])) {
                        $finishedTestpapers[$currentTestId] = array();
                    }
                    $finishedTestpapers[$currentTestId][] = $target;
                }
            }
        }

        if (!empty($reviewingTargets)) {
            $currentTestId = 0;
            foreach ($reviewingTargets as $target) {
                if ($currentTestId == 0 || $currentTestId != $target['testId']) {
                    $currentTestId = $target['testId'];
                }
                if ($target['type'] == 'homework') {
                    if (empty($reviewingHomeworks[$currentTestId])) {
                        $reviewingHomeworks[$currentTestId] = array();
                    }
                    $reviewingHomeworks[$currentTestId][] = $target;
                } else {
                    if (empty($reviewingTestpapers[$currentTestId])) {
                        $reviewingTestpapers[$currentTestId] = array();
                    }
                    $reviewingTestpapers[$currentTestId][] = $target;
                }
            }
        }

        goto result;

        result:
        // $reportCard['tasks']                 = $tasks;
        $reportCard['allHomeworks']          = $allHomeworks;
        $reportCard['finishedHomeworks']     = $finishedHomeworks;
        $reportCard['reviewingHomeworks']    = $reviewingHomeworks;
        $reportCard['bestHomeworks']         = $bestHomeworks;
        $reportCard['allTestpapers']         = $allTestpapers;
        $reportCard['finishedTestpapers']    = $finishedTestpapers;
        $reportCard['reviewingTestpapers']   = $reviewingTestpapers;
        $reportCard['bestTestpapers']        = $bestTestpapers;
        $reportCard['allFinishedHomeworks']  = array_sum($finishedHomeworks);
        $reportCard['allFinishedTestpapers'] = array_sum($finishedTestpapers);
        // var_dump($allTestpapers);exit;
        return $reportCard;
    }

    private function gradeBetterThan($source, $target)
    {
        if (empty($target)) {
            return true;
        }

        $levels = array('excellent', 'good', 'passed', 'unpassed', 'none');
        $levels = array_values($levels);
        if (array_search($source['passedStatus'], $levels) < array_search($target['passedStatus'], $levels)) {
            return true;
        }
        return $source['score'] > $target['score'];
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
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }
}
