<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ExportHelp;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LearningDataAnalysisService;
use Biz\Course\Service\MemberService;
use Biz\MemberOperation\Service\MemberOperationService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\UserFieldService;
use Biz\User\Service\UserService;
use Codeages\Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class StudentManageController extends BaseController
{
    public function studentsAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $followings = $this->findCurrentUserFollowings();

        $keyword = $request->query->get('keyword', '');

        $conditions = array(
            'courseId' => $course['id'],
            'role' => 'student',
        );

        if (!empty($keyword)) {
            $conditions['userIds'] = $this->getUserService()->getUserIdsByKeyword($keyword);
        }

        $paginator = new Paginator(
            $request,
            $this->getCourseMemberService()->countMembers($conditions),
            20
        );

        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $this->appendLearningProgress($members);

        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('course-manage/student/index.html.twig', array(
            'courseSet' => $courseSet,
            'course' => $course,
            'students' => $members,
            'followings' => $followings,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function findCurrentUserFollowings()
    {
        $user = $this->getCurrentUser();
        $followings = $this->getUserService()->findAllUserFollowing($user->getId());
        if (!empty($followings)) {
            return ArrayToolkit::index($followings, 'id');
        }

        return array();
    }

    public function studentRecordsAction(Request $request, $courseSetId, $courseId, $type)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        return $this->render(
            'course-manage/student/records.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $course,
                'type' => $type,
            )
        );
    }

    public function createCourseStudentAction(Request $request, $courseSetId, $courseId)
    {
        $operateUser = $this->getUser();
        $courseSetting = $this->getSettingService()->get('course');
        if (!$operateUser->isAdmin() && empty($courseSetting['teacher_manage_student'])) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $user = $this->getUserService()->getUserByLoginField($data['queryfield']);

            $data['source'] = 'outside';
            $data['remark'] = empty($data['remark']) ? $operateUser['nickname'].'添加' : $data['remark'];
            $data['userId'] = $user['id'];
            $this->getCourseMemberService()->becomeStudentAndCreateOrder($user['id'], $courseId, $data);

            $this->setFlashMessage('success', 'site.add.success');

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_course_students',
                    array('courseSetId' => $courseSetId, 'courseId' => $courseId)
                )
            );
        }
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        return $this->render(
            'course-manage/student/add-modal.html.twig',
            array(
                'course' => $course,
                'courseSetId' => $courseSetId,
            )
        );
    }

    public function removeCourseStudentAction($courseSetId, $courseId, $userId)
    {
        $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $this->getCourseMemberService()->removeCourseStudent($courseId, $userId);

        return $this->createJsonResponse(array('success' => true));
    }

    public function remarkAction(Request $request, $courseSetId, $courseId, $userId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $user = $this->getUserService()->getUser($userId);
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $userId);

        if (empty($member)) {
            throw $this->createAccessDeniedException(sprintf('学员#%s不属于教学计划#%s', $userId, $courseId));
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $member = $this->getCourseMemberService()->remarkStudent($course['id'], $user['id'], $data['remark']);

            return $this->createJsonResponse(array('success' => 1));
        }
        $default = $this->getSettingService()->get('default', array());

        return $this->render(
            'course-manage/student/remark-modal.html.twig',
            array(
                'member' => $member,
                'user' => $user,
                'course' => $course,
                'default' => $default,
            )
        );
    }

    public function addMemberExpiryDaysAction(Request $request, $courseId, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $course = $this->getCourseService()->getCourse($courseId);
        if ($request->getMethod() === 'POST') {
            $fields = $request->request->all();
            $this->getCourseMemberService()->addMemberExpiryDays($courseId, $userId, $fields['expiryDay']);

            return $this->createJsonResponse(true);
        }
        $default = $this->getSettingService()->get('default', array());

        return $this->render(
            'course-manage/student/set-expiryday-modal.html.twig',
            array(
                'course' => $course,
                'user' => $user,
                'default' => $default,
            )
        );
    }

    public function checkStudentAction(Request $request, $courseSetId, $courseId)
    {
        $keyword = $request->query->get('value');
        $user = $this->getUserService()->getUserByLoginField($keyword);

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

    public function showAction(Request $request, $courseSetId, $courseId, $userId)
    {
        if (!$this->getCurrentUser()->isAdmin()) {
            throw $this->createAccessDeniedException('您无权查看学员详细信息！');
        }

        return $this->forward('AppBundle:Student:show', array(
            'request' => $request,
            'userId' => $userId,
        ));
    }

    public function definedShowAction(Request $request, $courseId, $userId)
    {
        if (!$this->getCurrentUser()->isAdmin()) {
            return $this->createMessageResponse('error', '您无权查看学员详细信息！');
        }

        $course = $this->getCourseService()->tryManageCourse($courseId);
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $userId);

        if (empty($member)) {
            return $this->createMessageResponse('error', sprintf('学员#%s不属于教学计划#%s的学员', $userId, $courseId));
        }

        return $this->forward('AppBundle:Student:definedShow', array(
            'request' => $request,
            'userId' => $userId,
        ));
    }

    public function studyProcessAction($courseSetId, $courseId, $userId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $student = $this->getCourseMemberService()->getCourseMember($courseId, $userId);
        if (empty($student)) {
            throw $this->createNotFoundException("Student#{$userId} Not Found");
        }
        $user = $this->getUserService()->getUser($student['userId']);

        $questionCount = $this->getCourseMemberService()->countQuestionsByCourseIdAndUserId($courseId, $userId);
        $activityCount = $this->getCourseMemberService()->countActivitiesByCourseIdAndUserId($courseId, $userId);
        $discussionCount = $this->getCourseMemberService()->countDiscussionsByCourseIdAndUserId($courseId, $userId);
        $postCount = $this->getCourseMemberService()->countPostsByCourseIdAndUserId($courseId, $userId);

        //@todo 统计学习XX天应换种方法
        list($daysCount, $learnedTime, $learnedTimePerDay) = $this
            ->getActivityLearnLogService()
            ->calcLearnProcessByCourseIdAndUserId($courseId, $userId);

        return $this->render(
            'course-manage/student/process-modal.html.twig',
            array(
                'course' => $course,
                'student' => $student,
                'user' => $user,
                'questionCount' => $questionCount,
                'activityCount' => $activityCount,
                'discussionCount' => $discussionCount,
                'postCount' => $postCount,
                'daysCount' => $daysCount,
                'learnedTime' => round($learnedTime / 60 / 60, 2, PHP_ROUND_HALF_EVEN),
                'learnedTimePerDay' => round($learnedTimePerDay / 60 / 60, 2, PHP_ROUND_HALF_EVEN),
            )
        );
    }

    public function reportCardAction($course, $user)
    {
        $reportCard = $this->createReportCard($course, $user);

        return $this->render('course-manage/student/report-card.html.twig', $reportCard);
    }

    public function exportCsvAction(Request $request, $courseSetId, $courseId)
    {
        $fileName = sprintf('course-%s-students-(%s).csv', $courseId, date('Y-n-d'));

        return ExportHelp::exportCsv($request, $fileName);
    }

    public function exportDatasAction(Request $request, $courseSetId, $courseId)
    {
        $courseSetting = $this->getSettingService()->get('course', array());
        if (!$this->hasAdminRole() || !empty($courseSetting['teacher_export_student'])) {
            $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        }

        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($request);

        list($title, $students, $courseMemberCount) = $this->getExportContent(
            $courseId,
            $start,
            $limit,
            $exportAllowCount
        );

        $file = '';
        if ($start == 0) {
            $file = ExportHelp::addFileTitle($request, 'course_students', $title);
        }

        $content = implode("\r\n", $students);
        $file = ExportHelp::saveToTempFile($request, $content, $file);

        $status = ExportHelp::getNextMethod($start + $limit, $courseMemberCount);

        return $this->createJsonResponse(
            array(
                'status' => $status,
                'fileName' => $file,
                'start' => $start + $limit,
            )
        );
    }

    protected function getExportContent($id, $start, $limit, $exportAllowCount)
    {
        $gender = array(
            'female' => $this->getServiceKernel()->trans('女'),
            'male' => $this->getServiceKernel()->trans('男'),
            'secret' => $this->getServiceKernel()->trans('秘密'),
        );

        $course = $this->getCourseService()->getCourse($id);

        $condition = array(
            'courseId' => $course['id'],
            'role' => 'student',
        );

        $courseMemberCount = $this->getCourseMemberService()->countMembers($condition);

        $courseMemberCount = ($courseMemberCount > $exportAllowCount) ? $exportAllowCount : $courseMemberCount;
        if ($courseMemberCount < ($start + $limit + 1)) {
            $limit = $courseMemberCount - $start;
        }
        $courseMembers = $this->getCourseMemberService()->searchMembers(
            $condition,
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );
        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        $fields['weibo'] = $this->getServiceKernel()->trans('微博');

        foreach ($userFields as $userField) {
            $fields[$userField['fieldName']] = $userField['title'];
        }

        $studentUserIds = ArrayToolkit::column($courseMembers, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $this->appendLearningProgress($courseMembers);

        $str = $this->getServiceKernel()->trans('用户名,Email,加入学习时间,学习进度,姓名,性别,QQ号,微信号,手机号,公司,职业,头衔');

        foreach ($fields as $key => $value) {
            $str .= ','.$value;
        }

        $students = array();

        foreach ($courseMembers as $courseMember) {
            $member = '';
            $member .= $users[$courseMember['userId']]['nickname'].',';
            $member .= $users[$courseMember['userId']]['email'].',';
            $member .= date('Y-n-d H:i:s', $courseMember['createdTime']).',';
            $member .= $courseMember['learningProgressPercent'].',';
            $member .= $profiles[$courseMember['userId']]['truename'] ? $profiles[$courseMember['userId']]['truename'].',' : '-'.',';
            $member .= $gender[$profiles[$courseMember['userId']]['gender']].',';
            $member .= $profiles[$courseMember['userId']]['qq'] ? $profiles[$courseMember['userId']]['qq'].',' : '-'.',';
            $member .= $profiles[$courseMember['userId']]['weixin'] ? $profiles[$courseMember['userId']]['weixin'].',' : '-'.',';
            $member .= $profiles[$courseMember['userId']]['mobile'] ? $profiles[$courseMember['userId']]['mobile'].',' : '-'.',';
            $member .= $profiles[$courseMember['userId']]['company'] ? $profiles[$courseMember['userId']]['company'].',' : '-'.',';
            $member .= $profiles[$courseMember['userId']]['job'] ? $profiles[$courseMember['userId']]['job'].',' : '-'.',';
            $member .= $users[$courseMember['userId']]['title'] ? $users[$courseMember['userId']]['title'].',' : '-'.',';

            foreach ($fields as $key => $value) {
                $member .= $profiles[$courseMember['userId']][$key] ? $profiles[$courseMember['userId']][$key].',' : '-'.',';
            }

            $students[] = $member;
        }

        return array($str, $students, $courseMemberCount);
    }

    private function appendLearningProgress(&$members)
    {
        foreach ($members as &$member) {
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($member['courseId'], $member['userId']);
            $member['learningProgressPercent'] = $progress['percent'];
        }
    }

    protected function hasAdminRole()
    {
        $user = $this->getCurrentUser();
        if (in_array('ROLE_ADMIN', $user['roles']) || in_array('ROLE_SUPER_ADMIN', $user['roles'])) {
            return true;
        }

        return false;
    }

    private function createReportCard($course, $user)
    {
        $reportCard = array();

        //homeworks&testpapers合并处理，定义为：test(type=[homework,testpaper])
        $activities = array();
        $allTests = array();
        $finishedTests = array();
        $reviewingTests = array();
        $bestTests = array();
        $homeworksCount = 0;
        $testpapersCount = 0;
        $finishedHomeworksCount = 0;
        $finishedTestpapersCount = 0;

        $tasks = $this->getTaskService()->findTasksByCourseId($course['id']);

        if (empty($tasks)) {
            goto result;
        }
        $activitiyIds = ArrayToolkit::column($tasks, 'activityId');
        $activitiesWithMeta = $this->getActivityService()->findActivities($activitiyIds, true);

        foreach ($activitiesWithMeta as $activity) {
            if ($activity['mediaType'] === 'homework') {
                $homeworksCount += 1;
                $activities[] = array(
                    'id' => $activity['id'],
                    'mediaId' => $activity['mediaId'],
                    'name' => $activity['title'],
                );
            } elseif ($activity['mediaType'] === 'testpaper') {
                $testpapersCount += 1;
                $activities[] = array(
                    'id' => $activity['id'],
                    'mediaId' => $activity['ext']['mediaId'],
                    'name' => $activity['title'],
                );
            }
        }

        $finishedTargets = array();
        $reviewingTargets = array();
        if (!empty($activities)) {
            $testIds = ArrayToolkit::column($activities, 'mediaId');
            $allTests = $this->getTestpaperService()->searchTestpapers(
                array(
                    'ids' => $testIds,
                ),
                array('createdTime' => 'ASC'),
                0,
                PHP_INT_MAX
            );

            $finishedTargets = $this->getTestpaperService()->searchTestpaperResults(
                array(
                    'courseId' => $course['id'],
                    'userId' => $user['id'],
                    'status' => 'finished',
                    'types' => array('homework', 'testpaper'),
                ),
                array('lessonId' => 'ASC', 'beginTime' => 'ASC'),
                0,
                PHP_INT_MAX
            );

            $reviewingTargets = $this->getTestpaperService()->searchTestpaperResults(
                array(
                    'courseId' => $course['id'],
                    'userId' => $user['id'],
                    'status' => 'reviewing',
                    'types' => array('homework', 'testpaper'),
                ),
                array('lessonId' => 'ASC', 'beginTime' => 'ASC'),
                0,
                PHP_INT_MAX
            );
        }

        if (!empty($finishedTargets)) {
            $currentActivityId = 0;
            foreach ($finishedTargets as $target) {
                if ($currentActivityId == 0 || $currentActivityId != $target['lessonId']) {
                    $currentActivityId = $target['lessonId'];
                }
                if ($target['type'] === 'homework') {
                    $finishedHomeworksCount += 1;
                } else {
                    $finishedTestpapersCount += 1;
                }

                if (empty($bestTests[$currentActivityId])) {
                    $bestTests[$currentActivityId] = array();
                }
                if ($this->gradeBetterThan($target, $bestTests[$currentActivityId])) {
                    $bestTests[$currentActivityId] = $target;
                }

                if (empty($finishedTests[$currentActivityId])) {
                    $finishedTests[$currentActivityId] = array();
                }
                $finishedTests[$currentActivityId][] = $target;
            }
        }

        if (!empty($reviewingTargets)) {
            $currentActivityId = 0;
            foreach ($reviewingTargets as $target) {
                if ($currentActivityId == 0 || $currentActivityId != $target['lessonId']) {
                    $currentActivityId = $target['lessonId'];
                }
                if (empty($reviewingTests[$currentActivityId])) {
                    $reviewingTests[$currentActivityId] = array();
                }
                $reviewingTests[$currentActivityId][] = $target;
            }
        }

        goto result;

        result:
        $reportCard['activities'] = $activities;
        $reportCard['allTests'] = ArrayToolkit::index($allTests, 'id');
        $reportCard['finishedTests'] = $finishedTests;
        $reportCard['reviewingTests'] = $reviewingTests;
        $reportCard['bestTests'] = $bestTests;

        $reportCard['homeworksCount'] = $homeworksCount;
        $reportCard['testpapersCount'] = $testpapersCount;
        $reportCard['finishedHomeworksCount'] = $finishedHomeworksCount;
        $reportCard['finishedTestpapersCount'] = $finishedTestpapersCount;

        return $reportCard;
    }

    private function gradeBetterThan($source, $target)
    {
        if (empty($target)) {
            return true;
        }

        $levels = array('excellent', 'good', 'passed', 'unpassed', 'none');
        $levels = array_values($levels);
        $sourceIndex = array_search($source['passedStatus'], $levels);
        $targetIndex = array_search($target['passedStatus'], $levels);

        if ($sourceIndex < $targetIndex) {
            return true;
        }
        if ($sourceIndex === $targetIndex) {
            return $source['score'] >= $target['score'];
        }

        return false;
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

    /**
     * @return LearningDataAnalysisService
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->createService('Course:LearningDataAnalysisService');
    }

    /**
     * @return MemberOperationService
     */
    protected function getMemberOperationService()
    {
        return $this->createService('MemberOperation:MemberOperationService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
