<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Common\Paginator;
use AppBundle\Common\ExportHelp;
use Biz\Activity\ActivityException;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\LearningDataAnalysisService;
use Biz\Common\CommonException;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Order\Service\OrderService;
use Biz\Content\Service\FileService;
use Biz\Taxonomy\Service\TagService;
use Biz\Course\Service\CourseService;
use Biz\Testpaper\TestpaperException;
use Biz\Thread\Service\ThreadService;
use AppBundle\Common\ClassroomToolkit;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserFieldService;
use Biz\Task\Service\TaskResultService;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\User\Service\NotificationService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Biz\Classroom\Service\ClassroomReviewService;
use Biz\Activity\Service\TestpaperActivityService;

class ClassroomManageController extends BaseController
{
    public function indexAction($id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);
        $classroom['lessonNum'] = $this->getClassroomService()->countCourseTasksByClassroomId($id);

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        $courseIds = ArrayToolkit::column($courses, 'id');

        $currentTime = time();
        $todayTimeStart = strtotime(date('Y-m-d', $currentTime));
        $todayTimeEnd = strtotime(date('Y-m-d', $currentTime + 24 * 3600));

        $yesterdayTimeStart = strtotime(date('Y-m-d', $currentTime - 24 * 3600));
        $yesterdayTimeEnd = strtotime(date('Y-m-d', $currentTime));

        $todayFinishedTaskNum = 0;
        $yesterdayFinishedTaskNum = 0;

        if (!empty($courseIds)) {
            $todayFinishedTaskNum = $this->getTaskResultService()->countTaskResults(
                array(
                    'courseIds' => $courseIds,
                    'finishedTime_GE' => $todayTimeStart,
                    'finishedTime_LE' => $todayTimeEnd,
                    'status' => 'finish',
                )
            );
            $yesterdayFinishedTaskNum = $this->getTaskResultService()->countTaskResults(
                array(
                    'courseIds' => $courseIds,
                    'finishedTime_GE' => $yesterdayTimeStart,
                    'finishedTime_LE' => $yesterdayTimeEnd,
                    'status' => 'finish',
                )
            );
        }

        $todayThreadCount = $this->getThreadService()->searchThreadCount(
            array(
                'targetType' => 'classroom',
                'targetId' => $id,
                'type' => 'discussion',
                'startTime' => $todayTimeStart,
                'endTime' => $todayTimeEnd,
                'status' => 'open',
            )
        );
        $yesterdayThreadCount = $this->getThreadService()->searchThreadCount(
            array(
                'targetType' => 'classroom',
                'targetId' => $id,
                'type' => 'discussion',
                'startTime' => $yesterdayTimeStart,
                'endTime' => $yesterdayTimeEnd,
                'status' => 'open',
            )
        );

        $studentCount = $this->getClassroomService()->searchMemberCount(
            array(
                'role' => 'student',
                'classroomId' => $id,
                'startTimeGreaterThan' => $todayTimeStart,
            )
        );
        $auditorCount = $this->getClassroomService()->searchMemberCount(
            array(
                'role' => 'auditor',
                'classroomId' => $id,
                'startTimeGreaterThan' => $todayTimeStart,
            )
        );

        $allCount = $studentCount + $auditorCount;

        $yestodayStudentCount = $this->getClassroomService()->searchMemberCount(
            array(
                'role' => 'student',
                'classroomId' => $id,
                'startTimeLessThan' => $yesterdayTimeEnd,
                'startTimeGreaterThan' => $yesterdayTimeStart,
            )
        );
        $yestodayAuditorCount = $this->getClassroomService()->searchMemberCount(
            array(
                'role' => 'auditor',
                'classroomId' => $id,
                'startTimeLessThan' => $yesterdayTimeEnd,
                'startTimeGreaterThan' => $yesterdayTimeStart,
            )
        );

        $yestodayAllCount = $yestodayStudentCount + $yestodayAuditorCount;

        $reviewsNum = $this->getClassroomReviewService()->searchReviewCount(array('classroomId' => $id));
        $paginator = new Paginator(
            $this->get('request'),
            $reviewsNum,
            20
        );

        $reviews = $this->getClassroomReviewService()->searchReviews(
            array('classroomId' => $id, 'parentId' => 0),
            array('createdTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($reviews, 'userId');
        $reviewUsers = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'classroom-manage/index.html.twig',
            array(
                'classroom' => $classroom,
                'studentCount' => $studentCount,
                'yestodayStudentCount' => $yestodayStudentCount,
                'allCount' => $allCount,
                'yestodayAllCount' => $yestodayAllCount,
                'reviews' => $reviews,
                'reviewUsers' => $reviewUsers,
                'todayFinishedTaskNum' => $todayFinishedTaskNum,
                'yesterdayFinishedTaskNum' => $yesterdayFinishedTaskNum,
                'todayThreadCount' => $todayThreadCount,
                'yesterdayThreadCount' => $yesterdayThreadCount,
            )
        );
    }

    public function menuAction($classroom, $sideNav, $context)
    {
        $canManage = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $canHandle = $this->getClassroomService()->canHandleClassroom($classroom['id']);

        return $this->render(
            'classroom-manage/menu.html.twig',
            array(
                'canManage' => $canManage,
                'canHandle' => $canHandle,
                'side_nav' => $sideNav,
                'classroom' => $classroom,
                '_context' => $context,
            )
        );
    }

    public function studentsAction(Request $request, $id, $role = 'student')
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);
        $fields = $request->query->all();
        $condition = array();

        if (!empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserService()->getUserIdsByKeyword($fields['keyword']);
        }

        $condition = array_merge($condition, array('classroomId' => $id, 'role' => 'student'));

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount($condition),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            $condition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        $this->appendLearningProgress($students);

        return $this->render(
            'classroom-manage/student.html.twig',
            array(
                'classroom' => $classroom,
                'students' => $students,
                'users' => $users,
                'paginator' => $paginator,
                'role' => $role,
            )
        );
    }

    private function appendLearningProgress(&$classroomMembers)
    {
        foreach ($classroomMembers as &$classroomMember) {
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($classroomMember['classroomId'], $classroomMember['userId']);
            $classroomMember['learningProgressPercent'] = $progress['percent'];
        }
    }

    public function aduitorAction(Request $request, $id, $role = 'auditor')
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $fields = $request->query->all();
        $condition = array();

        if (!empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserService()->getUserIdsByKeyword($fields['keyword']);
        }

        $condition = array_merge($condition, array('classroomId' => $id, 'role' => 'auditor'));

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount($condition),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            $condition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        return $this->render(
            'classroom-manage/auditor.html.twig',
            array(
                'classroom' => $classroom,
                'students' => $students,
                'users' => $users,
                'paginator' => $paginator,
                'role' => $role,
            )
        );
    }

    public function recordAction(Request $request, $id, $type)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render(
            'classroom-manage/record/index.html.twig',
            array(
                'classroom' => $classroom,
                'type' => $type,
            )
        );
    }

    public function remarkAction(Request $request, $classroomId, $userId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);

        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $user = $this->getUserService()->getUser($userId);
        $member = $this->getClassroomService()->getClassroomMember($classroomId, $userId);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getClassroomService()->remarkStudent($classroom['id'], $user['id'], $data['remark']);

            return $this->createJsonResponse(array('success' => 1));
        }

        return $this->render(
            'classroom-manage/remark-modal.html.twig',
            array(
                'member' => $member,
                'user' => $user,
                'classroom' => $classroom,
            )
        );
    }

    public function removeAction($classroomId, $userId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);

        $this->getClassroomService()->removeStudent(
            $classroomId,
            $userId,
            array(
                'reason' => 'site.remove_by_manual',
                'reason_type' => 'remove',
            )
        );

        return $this->createJsonResponse(true);
    }

    public function createAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            if ($this->getClassroomService()->isClassroomOverDue($id)) {
                return $this->createJsonResponse(array('success' => 0, 'message' => $this->trans('classroom.joining_date.expired_tips')));
            }

            $user = $this->getUserService()->getUserByLoginField($data['queryfield']);

            if (empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }

            $data['remark'] = empty($data['remark']) ? '管理员添加' : $data['remark'];
            $data['isNotify'] = 1;
            $this->getClassroomService()->becomeStudentWithOrder($classroom['id'], $user['id'], $data);

            return $this->createJsonResponse(array('success' => 1));
        }

        return $this->render(
            'classroom-manage/create-modal.html.twig',
            array(
                'classroom' => $classroom,
            )
        );
    }

    public function checkStudentAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $keyWord = $request->query->get('value');
        $user = $this->getUserService()->getUserByLoginField($keyWord);
        $response = true;
        if (!$user) {
            $response = $this->container->get('translator')->trans('user.not_exist');
        } else {
            $isClassroomStudent = $this->getClassroomService()->isClassroomStudent($id, $user['id']);

            if ($isClassroomStudent) {
                $response = $this->container->get('translator')->trans('classroom.add_student.already_exists_tips');
            }
        }

        return $this->createJsonResponse($response);
    }

    public function exportDatasAction(Request $request, $id, $role)
    {
        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($request);

        list($title, $students, $classroomMemberCount) = $this->getExportContent(
            $id,
            $role,
            $start,
            $limit,
            $exportAllowCount
        );

        $file = '';
        if (0 == $start) {
            $file = ExportHelp::addFileTitle($request, 'classroom_'.$role.'_students', $title);
        }

        $content = implode("\r\n", $students);
        $file = ExportHelp::saveToTempFile($request, $content, $file);
        $status = ExportHelp::getNextMethod($start + $limit, $classroomMemberCount);

        return $this->createJsonResponse(
            array(
                'status' => $status,
                'fileName' => $file,
                'start' => $start + $limit,
            )
        );
    }

    public function exportCsvAction(Request $request, $id)
    {
        $role = $request->query->get('role');
        $fileName = sprintf('classroom-%s-%s-(%s).csv', $id, $role, date('Y-n-d'));

        return ExportHelp::exportCsv($request, $fileName);
    }

    private function getExportContent($id, $role, $start, $limit, $exportAllowCount)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $gender = array('female' => '女', 'male' => '男', 'secret' => '秘密');

        $classroom = $this->getClassroomService()->getClassroom($id);

        $condition = array(
            'classroomId' => $classroom['id'],
            'role' => 'student' == $role ? 'student' : 'auditor',
        );

        $classroomMemberCount = $this->getClassroomService()->searchMemberCount($condition);
        $classroomMemberCount = ($classroomMemberCount > $exportAllowCount) ? $exportAllowCount : $classroomMemberCount;
        if ($classroomMemberCount < ($start + $limit + 1)) {
            $limit = $classroomMemberCount - $start;
        }
        $classroomMembers = $this->getClassroomService()->searchMembers(
            $condition,
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        $fields['weibo'] = '微博';

        foreach ($userFields as $userField) {
            $fields[$userField['fieldName']] = $userField['title'];
        }

        $studentUserIds = ArrayToolkit::column($classroomMembers, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $this->appendLearningProgress($classroomMembers);

        $str = '用户名,Email,加入学习时间,学习进度,姓名,性别,QQ号,微信号,手机号,公司,职业,头衔';

        foreach ($fields as $key => $value) {
            $str .= ','.$value;
        }

        $students = array();

        foreach ($classroomMembers as $classroomMember) {
            $member = '';
            $member .= $users[$classroomMember['userId']]['nickname'].',';
            $member .= $users[$classroomMember['userId']]['email'].',';
            $member .= date('Y-n-d H:i:s', $classroomMember['createdTime']).',';
            $member .= $classroomMember['learningProgressPercent'].',';
            $member .= $profiles[$classroomMember['userId']]['truename'] ? $profiles[$classroomMember['userId']]['truename'].',' : '-'.',';
            $member .= $gender[$profiles[$classroomMember['userId']]['gender']].',';
            $member .= $profiles[$classroomMember['userId']]['qq'] ? $profiles[$classroomMember['userId']]['qq'].',' : '-'.',';
            $member .= $profiles[$classroomMember['userId']]['weixin'] ? $profiles[$classroomMember['userId']]['weixin'].',' : '-'.',';
            $member .= $profiles[$classroomMember['userId']]['mobile'] ? $profiles[$classroomMember['userId']]['mobile'].',' : '-'.',';
            $member .= $profiles[$classroomMember['userId']]['company'] ? $profiles[$classroomMember['userId']]['company'].',' : '-'.',';
            $member .= $profiles[$classroomMember['userId']]['job'] ? $profiles[$classroomMember['userId']]['job'].',' : '-'.',';
            $member .= $users[$classroomMember['userId']]['title'] ? $users[$classroomMember['userId']]['title'].',' : '-'.',';

            foreach ($fields as $key => $value) {
                $member .= $profiles[$classroomMember['userId']][$key] ? '"'.str_replace(array(PHP_EOL, '"'), '', $profiles[$classroomMember['userId']][$key]).'",' : '-'.',';
            }

            $students[] = $member;
        }

        return array($str, $students, $classroomMemberCount);
    }

    public function serviceAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if (!$this->isPluginInstalled('ClassroomPlan') && $classroom['service'] && in_array(
                'studyPlan',
                $classroom['service']
            )
        ) {
            unset($classroom['service']['studyPlan']);
        }

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            $data['service'] = empty($data['service']) ? null : $data['service'];

            $classroom = $this->getClassroomService()->updateClassroom($id, $data);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render(
            'classroom-manage/services.html.twig',
            array(
                'classroom' => $classroom,
            )
        );
    }

    public function studentShowAction(Request $request, $classroomId, $userId)
    {
        if (!$this->getCurrentUser()->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        return $this->forward('AppBundle:Student:show', array(
            'request' => $request,
            'userId' => $userId,
        ));
    }

    public function studentDefinedShowAction(Request $request, $classroomId, $userId)
    {
        $classroom = $this->getClassroomService()->tryManageClassroom($classroomId);
        $member = $this->getClassroomService()->getClassroomMember($classroomId, $userId);
        if (empty($member)) {
            $this->createNewException(ClassroomException::NOTFOUND_MEMBER());
        }

        return $this->forward('AppBundle:Student:definedShow', array(
            'request' => $request,
            'userId' => $userId,
        ));
    }

    public function setClassroomMemberDeadlineAction(Request $request, $classroomId, $userId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);

        $member = $this->getClassroomService()->getClassroomMember($classroomId, $userId);

        if ($request->isMethod('POST')) {
            $fields = $request->request->all();

            if (empty($fields['deadline'])) {
                $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
            }

            $deadline = ClassroomToolkit::buildMemberDeadline(array(
                'expiryMode' => 'date',
                'expiryValue' => strtotime($fields['deadline'].' 23:59:59'),
            ));

            $this->getClassroomService()->updateMemberDeadlineByMemberId($member['id'], array(
                'deadline' => $deadline,
            ));

            return $this->createJsonResponse(true);
        }

        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $user = $this->getUserService()->getUser($userId);

        return $this->render('classroom-manage/member/set-deadline-modal.html.twig', array(
            'classroom' => $classroom,
            'user' => $user,
            'member' => $member,
        ));
    }

    public function teachersAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        $fields = array();

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            if (isset($data['teacherIds'])) {
                $teacherIds = $data['teacherIds'];

                $fields = array('teacherIds' => $teacherIds);
            }

            if (isset($data['headTeacherId'])) {
                $fields['headTeacherId'] = $data['headTeacherId'];
                $this->getClassroomService()->addHeadTeacher($id, $fields['headTeacherId']);
            }

            if ($fields) {
                $classroom = $this->getClassroomService()->updateClassroom($id, $fields);
            }

            $this->setFlashMessage('success', 'site.save.success');
        }

        $teacherIds = $this->getClassroomService()->findTeachers($id);
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);

        $teacherItems = array();

        foreach ($teacherIds as $key => $teacherId) {
            $user = $teachers[$teacherId];
            $teacherItems[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->avatarPath($user, 'small'),
            );
        }

        $headTeacher = $this->getUserService()->getUser($classroom['headTeacherId']);

        return $this->render(
            'classroom-manage/teachers.html.twig',
            array(
                'classroom' => $classroom,
                'teachers' => $teachers,
                'teacherIds' => $teacherIds,
                'headTeacher' => $headTeacher,
                'teacherItems' => $teacherItems,
            )
        );
    }

    public function headteacherAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $headTeacherId = empty($data['ids']) ? 0 : $data['ids'][0];
            $this->getClassroomService()->addHeadTeacher($id, $headTeacherId);

            $this->setFlashMessage('success', 'site.save.success');
        }

        $classroom = $this->getClassroomService()->getClassroom($id);
        $headTeacher = $this->getUserService()->getUser($classroom['headTeacherId']);
        $newheadTeacher = array();

        if ($headTeacher) {
            $newheadTeacher[] = array(
                'id' => $headTeacher['id'],
                'nickname' => $headTeacher['nickname'],
                'avatar' => $this->getWebExtension()->avatarPath($headTeacher, 'small'),
            );
        }

        return $this->render(
            'classroom-manage/headteacher.html.twig',
            array(
                'classroom' => $classroom,
                'headTeacher' => $newheadTeacher,
            )
        );
    }

    public function assistantsAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $userIds = empty($data['ids']) ? array() : $data['ids'];

            $this->getClassroomService()->updateAssistants($id, $userIds);

            $this->setFlashMessage('success', 'site.save.success');
        }

        $assistantIds = $this->getClassroomService()->findAssistants($id);
        $users = $this->getUserService()->findUsersByIds($assistantIds);
        $sortedAssistants = array();

        foreach ($assistantIds as $key => $assistantId) {
            $user = $users[$assistantId];
            $sortedAssistants[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->avatarPath($user, 'small'),
            );
        }

        return $this->render(
            'classroom-manage/assistants.html.twig',
            array(
                'classroom' => $classroom,
                'assistants' => $sortedAssistants,
            )
        );
    }

    public function setInfoAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ($request->isMethod('POST')) {
            $class = $request->request->all();

            $class['tagIds'] = $this->getTagIdsFromRequest($request);

            if ('date' === $class['expiryMode']) {
                $class['expiryValue'] = strtotime($class['expiryValue'].' 23:59:59');
            } elseif ('forever' === $class['expiryMode']) {
                $class['expiryValue'] = 0;
            }

            $classroom = $this->getClassroomService()->updateClassroomInfo($id, $class);

            $this->setFlashMessage('success', 'site.save.success');
        }

        $tags = $this->getTagService()->findTagsByOwner(array(
            'ownerType' => 'classroom',
            'ownerId' => $id,
        ));

        return $this->render('classroom-manage/set-info.html.twig', array(
            'classroom' => $classroom,
            'tags' => ArrayToolkit::column($tags, 'name'),
        ));
    }

    public function setPriceAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('POST' == $request->getMethod()) {
            $class = $request->request->all();

            $this->setFlashMessage('success', 'site.save.success');

            $classroom = $this->getClassroomService()->updateClassroom($id, $class);
        }

        $coinPrice = 0;
        $price = 0;
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($id);

        $cashRate = $this->getCashRate();

        foreach ($courses as $course) {
            $coinPrice += $course['originPrice'] * $cashRate;
            $price += $course['originPrice'];
        }

        $courseNum = count($courses);

        return $this->render(
            'classroom-manage/set-price.html.twig',
            array(
                'price' => $price,
                'coinPrice' => $coinPrice,
                'courseNum' => $courseNum,
                'classroom' => $classroom,
            )
        );
    }

    public function expiryDateRuleAction()
    {
        return $this->render('classroom-manage/rule.html.twig');
    }

    public function setPictureAction($id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render(
            'classroom-manage/set-picture.html.twig',
            array(
                'classroom' => $classroom,
            )
        );
    }

    public function pictureCropAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('POST' == $request->getMethod()) {
            $options = $request->request->all();
            $this->getClassroomService()->changePicture($classroom['id'], $options['images']);

            return $this->redirect($this->generateUrl('classroom_manage_set_picture', array('id' => $classroom['id'])));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 525, 350);

        return $this->render(
            'classroom-manage/picture-crop.html.twig',
            array(
                'classroom' => $classroom,
                'pictureUrl' => $pictureUrl,
                'naturalSize' => $naturalSize,
                'scaledSize' => $scaledSize,
            )
        );
    }

    public function removeCourseAction($id, $courseId)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $this->getClassroomService()->deleteClassroomCourses($id, array($courseId));

        return $this->createJsonResponse(array('success' => true));
    }

    public function coursesAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $userIds = array();
        $coinPrice = 0;
        $price = 0;

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('POST' == $request->getMethod()) {
            $courseIds = $request->request->get('courseIds', array());

            $this->getClassroomService()->updateClassroomCourses($id, $courseIds);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect(
                $this->generateUrl(
                    'classroom_manage_courses',
                    array(
                        'id' => $id,
                    )
                )
            );
        }

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($id);

        $cashRate = $this->getCashRate();

        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);

            $coinPrice += $course['originPrice'] * $cashRate;
            $price += $course['originPrice'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'classroom-manage/courses.html.twig',
            array(
                'classroom' => $classroom,
                'courses' => $courses,
                'price' => $price,
                'coinPrice' => $coinPrice,
                'users' => $users,
            )
        );
    }

    public function coursesSelectAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $data = $request->request->all();

        $courseIds = array();
        if (isset($data['ids']) && '' != $data['ids']) {
            $ids = $data['ids'];
            $ids = explode(',', $ids);
            foreach ($ids as $cid) {
                //cid => courseSetId:courseId
                $tmp = explode(':', $cid);
                $courseIds[] = $tmp[1];
            }
        } else {
            return new Response('success');
        }

        $this->getClassroomService()->addCoursesToClassroom($id, $courseIds);
        $this->setFlashMessage('success', 'site.add.success');

        return new Response('success');
    }

    public function publishAction($id)
    {
        $this->getClassroomService()->publishClassroom($id);

        return new Response('success');
    }

    public function checkNameAction(Request $request)
    {
        $nickName = $request->request->get('name');
        $user = array();

        if ('' != $nickName) {
            $user = $this->getUserService()->searchUsers(
                array('nickname' => $nickName, 'roles' => 'ROLE_TEACHER'),
                array('createdTime' => 'DESC'),
                0,
                1
            );
        }

        $user = $user ? $user[0] : array();

        return $this->render(
            'classroom-manage/teacher-info.html.twig',
            array(
                'user' => $user,
            )
        );
    }

    public function closeAction($id)
    {
        $this->getClassroomService()->closeClassroom($id);

        return new Response('success');
    }

    public function importUsersAction($id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render(
            'classroom-manage/import.html.twig',
            array(
                'classroom' => $classroom,
            )
        );
    }

    public function excelDataImportAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('published' != $classroom['status']) {
            $this->createNewException(ClassroomException::UNPUBLISHED_CLASSROOM());
        }

        return $this->forward(
            'TopxiaWebBundle:Importer:importExcelData',
            array(
                'request' => $request,
                'targetId' => $id,
                'targetType' => 'classroom',
            )
        );
    }

    public function testpaperAction($id)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render(
            'classroom-manage/testpaper/index.html.twig',
            array(
                'classroom' => $classroom,
            )
        );
    }

    public function testpaperResultListAction($id, $testpaperId, $activityId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $activity = $this->getActivityService()->getActivity($activityId);
        if (!$activity) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }

        return $this->render(
            'classroom-manage/testpaper/result-list.html.twig',
            array(
                'classroom' => $classroom,
                'testpaper' => $testpaper,
                'isTeacher' => true,
                'activityId' => $activity['id'],
            )
        );
    }

    public function resultNextCheckAction($id, $activityId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $courses = $this->getClassroomService()->findCoursesByClassroomId($id);
        $courseIds = ArrayToolkit::column($courses, 'id');

        $activity = $this->getActivityService()->getActivity($activityId);

        if (empty($activity) || !in_array($activity['fromCourseId'], $courseIds)) {
            return $this->createMessageResponse('error', 'Activity not found');
        }

        $checkResult = $this->getTestpaperService()->getNextReviewingResult($courseIds, $activity['id'], $activity['mediaType']);

        if (empty($checkResult)) {
            $route = $this->getRedirectRoute('list', $activity['mediaType']);

            return $this->redirect($this->generateUrl($route, array('id' => $id)));
        }

        $route = $this->getRedirectRoute('check', $activity['mediaType']);

        return $this->redirect($this->generateUrl($route, array('id' => $id, 'resultId' => $checkResult['id'])));
    }

    public function homeworkAction($id)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render(
            'classroom-manage/homework/index.html.twig',
            array(
                'classroom' => $classroom,
                'isTeacher' => true,
            )
        );
    }

    public function testpaperCheckAction(Request $request, $id, $resultId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->forward(
            'AppBundle:Testpaper/Manage:check',
            array(
                'request' => $request,
                'resultId' => $resultId,
                'source' => 'classroom',
                'targetId' => $classroom['id'],
            )
        );
    }

    public function homeworkCheckAction(Request $request, $id, $resultId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->forward(
            'AppBundle:HomeworkManage:check',
            array(
                'request' => $request,
                'resultId' => $resultId,
                'source' => 'classroom',
                'targetId' => $classroom['id'],
            )
        );
    }

    public function resultGraphAction(Request $request, $id, $activityId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $courses = $this->getClassroomService()->findCoursesByClassroomId($id);
        $courseIds = ArrayToolkit::column($courses, 'id');

        $activity = $this->getActivityService()->getActivity($activityId);

        if (empty($activity) || !in_array($activity['fromCourseId'], $courseIds)) {
            return $this->createMessageResponse('error', 'Activity not found');
        }

        if ('homework' == $activity['mediaType']) {
            $controller = 'AppBundle:HomeworkManage:resultGraph';
        } else {
            $controller = 'AppBundle:Testpaper/Manage:resultGraph';
        }

        return $this->forward($controller, array(
            'activityId' => $activityId,
        ));
    }

    public function resultAnalysisAction(Request $request, $id, $activityId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $activity = $this->getActivityService()->getActivity($activityId);
        if (empty($activity) || !in_array($activity['mediaType'], array('homework', 'testpaper'))) {
            return $this->createMessageResponse('error', 'Argument invalid');
        }

        if ('homework' == $activity['mediaType']) {
            $controller = 'AppBundle:HomeworkManage:resultAnalysis';
        } else {
            $controller = 'AppBundle:Testpaper/Manage:resultAnalysis';
        }

        return $this->forward($controller, array(
            'activityId' => $activityId,
            'targetId' => $id,
            'targetType' => 'classroom',
            'studentNum' => $classroom['studentNum'],
        ));
    }

    protected function getRedirectRoute($mode, $type)
    {
        $routes = array(
            'list' => array(
                'testpaper' => 'classroom_manage_testpaper',
                'homework' => 'classroom_manage_homework',
            ),
            'check' => array(
                'testpaper' => 'classroom_manage_testpaper_check',
                'homework' => 'classroom_manage_homework_check',
            ),
        );

        return $routes[$mode][$type];
    }

    private function getTagIdsFromRequest($request)
    {
        $tags = $request->request->get('tags');
        $tags = explode(',', $tags);
        $tags = $this->getTagService()->findTagsByNames($tags);

        return ArrayToolkit::column($tags, 'id');
    }

    protected function getCashRate()
    {
        $coinSetting = $this->getSettingService()->get('coin');
        $coinEnable = isset($coinSetting['coin_enabled']) && 1 == $coinSetting['coin_enabled'];
        $cashRate = $coinEnable && isset($coinSetting['cash_rate']) ? $coinSetting['cash_rate'] : 1;

        return $cashRate;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ClassroomReviewService
     */
    protected function getClassroomReviewService()
    {
        return $this->createService('Classroom:ClassroomReviewService');
    }

    /**
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->createService('Vip:Vip:LevelService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
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
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
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
     * @return LearningDataAnalysisService
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->createService('Classroom:LearningDataAnalysisService');
    }

    protected function getMemberOperationService()
    {
        return $this->createService('MemberOperation:MemberOperationService');
    }
}
