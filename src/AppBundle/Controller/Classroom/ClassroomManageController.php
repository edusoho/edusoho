<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ExportHelp;
use AppBundle\Common\Paginator;
use AppBundle\Common\TimeMachine;
use AppBundle\Controller\BaseController;
use Biz\Activity\ActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\LearningDataAnalysisService;
use Biz\Content\Service\FileService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Review\Service\ReviewService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\TagService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\TestpaperException;
use Biz\Thread\Service\ThreadService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserFieldService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
                [
                    'courseIds' => $courseIds,
                    'finishedTime_GE' => $todayTimeStart,
                    'finishedTime_LE' => $todayTimeEnd,
                    'status' => 'finish',
                ]
            );
            $yesterdayFinishedTaskNum = $this->getTaskResultService()->countTaskResults(
                [
                    'courseIds' => $courseIds,
                    'finishedTime_GE' => $yesterdayTimeStart,
                    'finishedTime_LE' => $yesterdayTimeEnd,
                    'status' => 'finish',
                ]
            );
        }

        $todayThreadCount = $this->getThreadService()->searchThreadCount(
            [
                'targetType' => 'classroom',
                'targetId' => $id,
                'type' => 'discussion',
                'startTime' => $todayTimeStart,
                'endTime' => $todayTimeEnd,
                'status' => 'open',
            ]
        );
        $yesterdayThreadCount = $this->getThreadService()->searchThreadCount(
            [
                'targetType' => 'classroom',
                'targetId' => $id,
                'type' => 'discussion',
                'startTime' => $yesterdayTimeStart,
                'endTime' => $yesterdayTimeEnd,
                'status' => 'open',
            ]
        );

        $studentCount = $this->getClassroomService()->searchMemberCount(
            [
                'role' => 'student',
                'classroomId' => $id,
                'startTimeGreaterThan' => $todayTimeStart,
            ]
        );
        $auditorCount = $this->getClassroomService()->searchMemberCount(
            [
                'role' => 'auditor',
                'classroomId' => $id,
                'startTimeGreaterThan' => $todayTimeStart,
            ]
        );

        $allCount = $studentCount + $auditorCount;

        $yestodayStudentCount = $this->getClassroomService()->searchMemberCount(
            [
                'role' => 'student',
                'classroomId' => $id,
                'startTimeLessThan' => $yesterdayTimeEnd,
                'startTimeGreaterThan' => $yesterdayTimeStart,
            ]
        );
        $yestodayAuditorCount = $this->getClassroomService()->searchMemberCount(
            [
                'role' => 'auditor',
                'classroomId' => $id,
                'startTimeLessThan' => $yesterdayTimeEnd,
                'startTimeGreaterThan' => $yesterdayTimeStart,
            ]
        );

        $yestodayAllCount = $yestodayStudentCount + $yestodayAuditorCount;

        $reviewConditions = ['targetType' => 'classroom', 'targetId' => $id, 'parentId' => 0];
        $reviewsNum = $this->getReviewService()->countReviews($reviewConditions);
        $paginator = new Paginator(
            $this->get('request'),
            $reviewsNum,
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $reviewConditions,
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($reviews, 'userId');
        $reviewUsers = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'classroom-manage/index.html.twig',
            [
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
            ]
        );
    }

    public function menuAction($classroom, $sideNav, $context)
    {
        $canManage = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $canHandle = $this->getClassroomService()->canHandleClassroom($classroom['id']);

        return $this->render(
            'classroom-manage/menu.html.twig',
            [
                'canManage' => $canManage,
                'canHandle' => $canHandle,
                'side_nav' => $sideNav,
                'classroom' => $classroom,
                '_context' => $context,
            ]
        );
    }

    public function studentsAction(Request $request, $id, $role = 'student')
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $fields = $request->query->all();
        $condition = [];

        if (!empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserService()->getUserIdsByKeyword($fields['keyword']);
        }

        $condition = array_merge($condition, ['classroomId' => $id, 'role' => 'student']);

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount($condition),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            $condition,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $this->appendLearningProgress($students);

        return $this->render(
            'classroom-manage/student.html.twig',
            [
                'classroom' => $this->getClassroomService()->getClassroom($id),
                'students' => $students,
                'users' => $this->getUserService()->findUsersByIds(array_column($students, 'userId')),
                'paginator' => $paginator,
                'role' => $role,
            ]
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
        $condition = [];

        if (!empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserService()->getUserIdsByKeyword($fields['keyword']);
        }

        $condition = array_merge($condition, ['classroomId' => $id, 'role' => 'auditor']);

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount($condition),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            $condition,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        return $this->render(
            'classroom-manage/auditor.html.twig',
            [
                'classroom' => $classroom,
                'students' => $students,
                'users' => $users,
                'paginator' => $paginator,
                'role' => $role,
            ]
        );
    }

    public function recordAction(Request $request, $id, $type)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render(
            'classroom-manage/record/index.html.twig',
            [
                'classroom' => $classroom,
                'type' => $type,
            ]
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

            return $this->createJsonResponse(['success' => 1]);
        }

        return $this->render(
            'classroom-manage/remark-modal.html.twig',
            [
                'member' => $member,
                'user' => $user,
                'classroom' => $classroom,
            ]
        );
    }

    public function removeAction($classroomId, $userId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);

        $this->getClassroomService()->removeStudent(
            $classroomId,
            $userId,
            [
                'reason' => 'site.remove_by_manual',
                'reason_type' => 'remove',
            ]
        );

        return $this->createJsonResponse(true);
    }

    public function removeStudentsAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $studentIds = $request->request->get('studentIds', []);
        if (empty($this->getUserService()->findUsersByIds($studentIds))) {
            return $this->createJsonResponse(['success' => false]);
        }

        $this->getClassroomService()->removeStudents($id, $studentIds, [
            'reason' => 'site.remove_by_manual',
            'reason_type' => 'remove',
        ]);

        return $this->createJsonResponse(['success' => true]);
    }

    public function createAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            if ($this->getClassroomService()->isClassroomOverDue($id)) {
                return $this->createJsonResponse(['success' => 0, 'message' => $this->trans('classroom.joining_date.expired_tips')]);
            }

            $user = $this->getUserService()->getUserByLoginField($data['queryfield'], true);

            if (empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }

            $data['remark'] = empty($data['remark']) ? '管理员添加' : $data['remark'];
            $data['isNotify'] = 1;
            $this->getClassroomService()->becomeStudentWithOrder($classroom['id'], $user['id'], $data);

            return $this->createJsonResponse(['success' => 1]);
        }

        return $this->render(
            'classroom-manage/create-modal.html.twig',
            [
                'classroom' => $classroom,
            ]
        );
    }

    public function checkStudentAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $keyWord = $request->query->get('value');
        $user = $this->getUserService()->getUserByLoginField($keyWord, true);
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
            [
                'status' => $status,
                'fileName' => $file,
                'start' => $start + $limit,
            ]
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
        $gender = ['female' => '女', 'male' => '男', 'secret' => '秘密'];

        $classroom = $this->getClassroomService()->getClassroom($id);

        $condition = [
            'classroomId' => $classroom['id'],
            'role' => 'student' == $role ? 'student' : 'auditor',
        ];

        $classroomMemberCount = $this->getClassroomService()->searchMemberCount($condition);
        $classroomMemberCount = ($classroomMemberCount > $exportAllowCount) ? $exportAllowCount : $classroomMemberCount;
        if ($classroomMemberCount < ($start + $limit + 1)) {
            $limit = $classroomMemberCount - $start;
        }
        $classroomMembers = $this->getClassroomService()->searchMembers(
            $condition,
            ['createdTime' => 'DESC'],
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

        $students = [];

        foreach ($classroomMembers as $classroomMember) {
            $member = '';
            $member .= $users[$classroomMember['userId']]['nickname']."\t".',';
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
                $member .= $profiles[$classroomMember['userId']][$key] ? '"'.str_replace([PHP_EOL, '"'], '', $profiles[$classroomMember['userId']][$key]).'",' : '-'.',';
            }

            $students[] = $member;
        }

        return [$str, $students, $classroomMemberCount];
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
            [
                'classroom' => $classroom,
            ]
        );
    }

    public function studentShowAction(Request $request, $classroomId, $userId)
    {
        if (!$this->getCurrentUser()->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        return $this->forward('AppBundle:Student:show', [
            'request' => $request,
            'userId' => $userId,
        ]);
    }

    public function studentDefinedShowAction(Request $request, $classroomId, $userId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);
        $member = $this->getClassroomService()->getClassroomMember($classroomId, $userId);
        if (empty($member)) {
            $this->createNewException(ClassroomException::NOTFOUND_MEMBER());
        }

        return $this->forward('AppBundle:Student:definedShow', [
            'request' => $request,
            'userId' => $userId,
        ]);
    }

    public function setClassroomMemberDeadlineAction(Request $request, $classroomId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);

        $userIds = $request->query->get('userIds', '');
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);

        if ($request->isMethod('POST')) {
            $fields = $request->request->all();

            if ('day' == $fields['updateType']) {
                $this->getClassroomService()->updateMembersDeadlineByDay($classroomId, $userIds, $fields['day'], $fields['waveType']);

                return $this->createJsonResponse(true);
            }
            $this->getClassroomService()->updateMembersDeadlineByDate($classroomId, $userIds, $fields['deadline']);

            return $this->createJsonResponse(true);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('classroom-manage/member/set-deadline-modal.html.twig', [
            'classroom' => $this->getClassroomService()->getClassroom($classroomId),
            'users' => $users,
            'userIds' => array_column($users, 'id'),
        ]);
    }

    public function checkDeadlineAction(Request $request, $classroomId)
    {
        $deadline = $request->query->get('deadline');
        $deadline = TimeMachine::isTimestamp($deadline) ? $deadline : strtotime($deadline.' 23:59:59');
        $userIds = $request->query->get('userIds');
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);
        if ($this->getClassroomService()->checkDeadlineForUpdateDeadline($classroomId, $userIds, $deadline)) {
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function checkDayAction(Request $request, $classroomId)
    {
        $waveType = $request->query->get('waveType');
        $day = $request->query->get('day');
        $userIds = $request->query->get('userIds');
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);
        if ($this->getClassroomService()->checkDayAndWaveTypeForUpdateDeadline($classroomId, $userIds, $day, $waveType)) {
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function teachersAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        $fields = [];

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            if (isset($data['teacherIds'])) {
                $teacherIds = $data['teacherIds'];

                $fields = ['teacherIds' => $teacherIds];
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

        $teacherItems = [];

        foreach ($teacherIds as $key => $teacherId) {
            $user = $teachers[$teacherId];
            $teacherItems[] = [
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->avatarPath($user, 'small'),
            ];
        }

        $headTeacher = $this->getUserService()->getUser($classroom['headTeacherId']);

        return $this->render(
            'classroom-manage/teachers.html.twig',
            [
                'classroom' => $classroom,
                'teachers' => $teachers,
                'teacherIds' => $teacherIds,
                'headTeacher' => $headTeacher,
                'teacherItems' => $teacherItems,
            ]
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
        $newheadTeacher = [];

        if ($headTeacher) {
            $newheadTeacher[] = [
                'id' => $headTeacher['id'],
                'nickname' => $headTeacher['nickname'],
                'avatar' => $this->getWebExtension()->avatarPath($headTeacher, 'small'),
            ];
        }

        return $this->render(
            'classroom-manage/headteacher.html.twig',
            [
                'classroom' => $classroom,
                'headTeacher' => $newheadTeacher,
            ]
        );
    }

    public function assistantsAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $userIds = empty($data['ids']) ? [] : $data['ids'];

            $this->getClassroomService()->updateAssistants($id, $userIds);

            $this->setFlashMessage('success', 'site.save.success');
        }

        $assistantIds = $this->getClassroomService()->findAssistants($id);
        $users = $this->getUserService()->findUsersByIds($assistantIds);
        $sortedAssistants = [];

        foreach ($assistantIds as $key => $assistantId) {
            $user = $users[$assistantId];
            $sortedAssistants[] = [
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->avatarPath($user, 'small'),
            ];
        }

        return $this->render(
            'classroom-manage/assistants.html.twig',
            [
                'classroom' => $classroom,
                'assistants' => $sortedAssistants,
            ]
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

        $tags = $this->getTagService()->findTagsByOwner([
            'ownerType' => 'classroom',
            'ownerId' => $id,
        ]);

        return $this->render('classroom-manage/set-info.html.twig', [
            'classroom' => $classroom,
            'tags' => ArrayToolkit::column($tags, 'name'),
        ]);
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
            [
                'price' => $price,
                'coinPrice' => $coinPrice,
                'courseNum' => $courseNum,
                'classroom' => $classroom,
            ]
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
            [
                'classroom' => $classroom,
            ]
        );
    }

    public function pictureCropAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('POST' == $request->getMethod()) {
            $options = $request->request->all();
            $this->getClassroomService()->changePicture($classroom['id'], $options['images']);

            return $this->redirect($this->generateUrl('classroom_manage_set_picture', ['id' => $classroom['id']]));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 540, 304);

        return $this->render(
            'classroom-manage/picture-crop.html.twig',
            [
                'classroom' => $classroom,
                'pictureUrl' => $pictureUrl,
                'naturalSize' => $naturalSize,
                'scaledSize' => $scaledSize,
            ]
        );
    }

    public function removeCourseAction($id, $courseId)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $this->getClassroomService()->deleteClassroomCourses($id, [$courseId]);

        return $this->createJsonResponse(['success' => true]);
    }

    public function deleteCourseSetAction(Request $request, $classroomId, $courseSetId, $courseId)
    {
        $currentUser = $this->getUser();

        if (!$currentUser->hasPermission('admin_v2_course_set_delete')) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        if ('draft' == $courseSet['status']) {
            $this->getCourseSetService()->deleteCourseSet($courseSetId);

            return $this->createJsonResponse(['code' => 0, 'message' => '删除课程成功']);
        }

        $isCheckPasswordLifeTime = $request->getSession()->get('checkPassword');
        if (!$isCheckPasswordLifeTime || $isCheckPasswordLifeTime < time()) {
            return $this->render('check-password/check-password-modal.twig', ['jsonp' => $request->query->get('jsonp')]);
        }

        $this->getClassroomService()->deleteClassroomCourses($classroomId, [$courseId]);

        $this->getCourseSetService()->deleteCourseSet($courseSetId);

        return $this->createJsonResponse(['code' => 0, 'message' => '删除课程成功']);
    }

    public function coursesAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $userIds = [];
        $coinPrice = 0;
        $price = 0;
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($id);
        $cashRate = $this->getCashRate();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);

            $coinPrice += $course['originPrice'] * $cashRate;
            $price += $course['originPrice'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render(
            'classroom-manage/courses.html.twig',
            [
                'classroom' => $classroom,
                'courses' => $courses,
                'price' => $price,
                'coinPrice' => $coinPrice,
                'users' => $users,
            ]
        );
    }

    public function courseItemsSortAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $courseIds = $request->request->get('courseIds');
        if (empty($courseIds)) {
            return $this->createJsonResponse(['result' => false]);
        }

        $this->getClassroomService()->updateClassroomCourses($id, $courseIds);

        return $this->createJsonResponse(['result' => true]);
    }

    public function coursesSelectAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $data = $request->request->all();

        $courseIds = [];
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
        $user = [];

        if ('' != $nickName) {
            $user = $this->getUserService()->searchUsers(
                ['nickname' => $nickName, 'roles' => 'ROLE_TEACHER'],
                ['createdTime' => 'DESC'],
                0,
                1
            );
        }

        $user = $user ? $user[0] : [];

        return $this->render(
            'classroom-manage/teacher-info.html.twig',
            [
                'user' => $user,
            ]
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
            [
                'classroom' => $classroom,
            ]
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
            [
                'request' => $request,
                'targetId' => $id,
                'targetType' => 'classroom',
            ]
        );
    }

    public function testpaperAction($id)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render(
            'classroom-manage/testpaper/index.html.twig',
            [
                'classroom' => $classroom,
            ]
        );
    }

    public function testpaperResultListAction($id, $testpaperId, $activityId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $testpaper = $this->getAssessmentService()->getAssessment($testpaperId);
        if (!$testpaper) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $activity = $this->getActivityService()->getActivity($activityId);
        if (!$activity) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }

        return $this->render(
            'classroom-manage/testpaper/result-list.html.twig',
            [
                'classroom' => $classroom,
                'testpaper' => $testpaper,
                'isTeacher' => true,
                'activity' => $activity,
                'activityId' => $activity['id'],
            ]
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

        $answerScene = $this->getAnswerSceneByActivity($activity);
        $answerRecord = $this->getAnswerRecordService()->getNextReviewingAnswerRecordByAnswerSceneId($answerScene['id']);

        if (empty($answerRecord)) {
            $route = $this->getRedirectRoute('list', $activity['mediaType']);

            return $this->redirect($this->generateUrl($route, ['id' => $id]));
        }

        $route = $this->getRedirectRoute('check', $activity['mediaType']);

        return $this->redirect($this->generateUrl($route, ['id' => $id, 'answerRecordId' => $answerRecord['id']]));
    }

    protected function getAnswerSceneByActivity($activity)
    {
        if ('testpaper' == $activity['mediaType']) {
            $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

            return $this->getAnswerSceneService()->get($testpaperActivity['answerSceneId']);
        }

        if ('homework' == $activity['mediaType']) {
            $homeworkActivity = $this->getHomeworkActivityService()->get($activity['mediaId']);

            return $this->getAnswerSceneService()->get($homeworkActivity['answerSceneId']);
        }
    }

    public function homeworkAction($id)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render(
            'classroom-manage/homework/index.html.twig',
            [
                'classroom' => $classroom,
                'isTeacher' => true,
            ]
        );
    }

    public function testpaperCheckAction(Request $request, $id, $answerRecordId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->forward(
            'AppBundle:Testpaper/Manage:check',
            [
                'request' => $request,
                'answerRecordId' => $answerRecordId,
                'source' => 'classroom',
                'targetId' => $classroom['id'],
            ]
        );
    }

    public function homeworkCheckAction(Request $request, $id, $answerRecordId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->forward(
            'AppBundle:HomeworkManage:check',
            [
                'request' => $request,
                'answerRecordId' => $answerRecordId,
                'source' => 'classroom',
                'targetId' => $classroom['id'],
            ]
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

        return $this->forward($controller, [
            'activityId' => $activityId,
        ]);
    }

    public function resultAnalysisAction(Request $request, $id, $activityId)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $activity = $this->getActivityService()->getActivity($activityId);
        if (empty($activity) || !in_array($activity['mediaType'], ['homework', 'testpaper'])) {
            return $this->createMessageResponse('error', 'Argument invalid');
        }

        if ('homework' == $activity['mediaType']) {
            $controller = 'AppBundle:HomeworkManage:resultAnalysis';
        } else {
            $controller = 'AppBundle:Testpaper/Manage:resultAnalysis';
        }

        return $this->forward($controller, [
            'activityId' => $activityId,
            'targetId' => $id,
            'targetType' => 'classroom',
            'studentNum' => $classroom['studentNum'],
        ]);
    }

    protected function getRedirectRoute($mode, $type)
    {
        $routes = [
            'list' => [
                'testpaper' => 'classroom_manage_testpaper',
                'homework' => 'classroom_manage_homework',
            ],
            'check' => [
                'testpaper' => 'classroom_manage_testpaper_check',
                'homework' => 'classroom_manage_homework_check',
            ],
        ];

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
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Review:ReviewService');
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
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
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

    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }
}
