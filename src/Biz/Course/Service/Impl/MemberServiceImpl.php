<?php

namespace Biz\Course\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TimeMachine;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\MemberException;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Course\Util\CourseTitleUtils;
use Biz\Goods\Service\GoodsService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Order\OrderException;
use Biz\Product\Service\ProductService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Order\Service\OrderRefundService;
use Codeages\Biz\Order\Service\OrderService;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Vip\Service\VipService;

/**
 * Class MemberServiceImpl
 * 所有api 均迁移自 courseService 中的对member操作的api.
 */
class MemberServiceImpl extends BaseService implements MemberService
{
    const ASSISTANT_LIMIT_NUM = 20;

    public function becomeStudentAndCreateOrder($userId, $courseId, $data)
    {
        //        $data = ArrayToolkit::parts($data, array('price', 'amount', 'remark', 'isAdminAdded', 'source'));

        if (!ArrayToolkit::requireds($data, ['price', 'remark'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $this->getCourseService()->tryManageCourse($courseId);

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $user = $this->getUserService()->getUserByUUID($userId);
            if(empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        if ($this->isCourseStudent($course['id'], $user['id'])) {
            $this->createNewException(MemberException::DUPLICATE_MEMBER());
        }

        /*S2B2C-CUSTOM*/
        if ('supplier' == $course['platform']) {
            $this->validateSourceCourseStatus($courseId);
        }
        /*END*/

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        // todo source不应该根据isAdminAdded判断，直接传入source
        if (isset($data['isAdminAdded']) && 1 == $data['isAdminAdded']) {
            $data['source'] = 'outside';
        }

        if (empty($data['price'])) {
            $data['price'] = 0;
        }

        try {
            $this->beginTransaction();
            if ($data['price'] > 0) {
                $product = $this->getProductService()->getProductByTargetIdAndType($course['courseSetId'], 'course');
                $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($product['id'], $course['id']);
                $order = $this->createOrder($goodsSpecs['id'], $user['id'], $data);
            } else {
                $order = ['id' => 0];
                $info = [
                    'orderId' => $order['id'],
                    'remark' => $data['remark'],
                    'reason' => 'site.join_by_import',
                    'reason_type' => 'import_join',
                ];
                $this->becomeStudent($course['id'], $user['id'], $info);
            }

            $member = $this->getCourseMember($course['id'], $user['id']);

            $currentUser = $this->getCurrentUser();
            if (isset($data['isAdminAdded']) && 1 == $data['isAdminAdded']) {
                $message = [
                    'courseId' => $course['id'],
                    'courseTitle' => $courseSet['title'],
                    'userId' => $currentUser['id'],
                    'userName' => $currentUser['nickname'],
                    'type' => 'create',
                ];
                $this->getNotificationService()->notify($member['userId'], 'student-create', $message);
            }

            $infoData = [
                'courseSetId' => $courseSet['id'],
                'courseId' => $course['id'],
                'title' => CourseTitleUtils::getDisplayedTitle($course),
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'remark' => $data['remark'],
            ];

            $this->getLogService()->info(
                'course',
                'add_student',
                "《{$courseSet['title']}》-{$course['title']}(#{$course['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$data['remark']}",
                $infoData
            );
            $this->commit();

            return [$course, $member, $order];
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    // 管理员，老师手动移除学员
    public function removeCourseStudent($courseId, $userId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            $user = $this->getUserService()->getUserByUUID($userId);
            if(empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        }
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);
        if (empty($member)) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }
        if ('student' !== $member['role']) {
            $this->createNewException(MemberException::MEMBER_NOT_STUDENT());
        }

        $result = $this->removeMember(
            $member,
            [
                'reason' => 'course.member.operation.admin_remove_course_student',
                'reason_type' => 'remove',
            ]
        );

        $course = $this->getCourseService()->getCourse($courseId);

        $infoData = [
            'courseId' => $course['id'],
            'title' => CourseTitleUtils::getDisplayedTitle($course),
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
        ];

        $this->getLogService()->info(
            'course',
            'remove_student',
            "教学计划《{$course['title']}》(#{$course['id']})，移除学员{$user['nickname']}(#{$user['id']})",
            $infoData
        );

        $this->dispatchEvent('course.quit', $course, ['userId' => $userId, 'member' => $member]);

        if ($this->getCurrentUser()->isAdmin()) {
            $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            $this->getNotificationService()->notify(
                $member['userId'],
                'student-remove',
                [
                    'courseId' => $course['id'],
                    'courseTitle' => $courseSet['title'],
                ]
            );
        }

        return $result;
    }

    public function removeCourseStudents($courseId, array $userIds)
    {
        foreach ($userIds as $userId) {
            $this->removeCourseStudent($courseId, $userId);
        }

        return true;
    }

    public function searchMembers($conditions, $orderBy, $start, $limit, $columns = [])
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getMemberDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function countMembers($conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getMemberDao()->count($conditions);
    }

    public function stickMyCourseByCourseSetId($courseSetId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        return $this->getMemberDao()->update(
            ['courseSetId' => $courseSet['id'], 'userId' => $user['id'], 'role' => 'teacher'],
            ['stickyTime' => time()]
        );
    }

    public function unStickMyCourseByCourseSetId($courseSetId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        return $this->getMemberDao()->update(
            ['courseSetId' => $courseSet['id'], 'userId' => $user['id'], 'role' => 'teacher'],
            ['stickyTime' => 0]
        );
    }

    public function findWillOverdueCourses()
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $condition = [
            'userId' => $currentUser['id'],
            'role' => 'student',
            'deadlineNotified' => 0,
            'deadlineGreaterThan' => time(),
        ];
        $courseMembers = $this->getMemberDao()->search($condition, ['createdTime' => 'ASC'], 0, 10);
        $courseIds = ArrayToolkit::column($courseMembers, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $courseMembers = ArrayToolkit::index($courseMembers, 'courseId');

        $shouldNotifyCourses = [];
        $shouldNotifyCourseMembers = [];

        $currentTime = time();

        foreach ($courses as $key => $course) {
            $courseMember = $courseMembers[$course['id']];

            /*
             * 去掉了$course['expiryDays'] > 0 &&
             */
            if ($currentTime < $courseMember['deadline'] && (10 * 24 * 60 * 60 + $currentTime) > $courseMember['deadline']) {
                $shouldNotifyCourses[] = $course;
                $shouldNotifyCourseMembers[] = $courseMember;
            }
        }

        return [$shouldNotifyCourses, $shouldNotifyCourseMembers];
    }

    public function getCourseMember($courseId, $userId)
    {
        return $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);
    }

    public function waveMember($id, $diffs)
    {
        return $this->getMemberDao()->wave([$id], $diffs);
    }

    public function searchMemberIds($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareConditions($conditions);

        if (is_array($sort)) {
            $orderBy = $sort;
        } else {
            $orderBy = ['createdTime' => 'DESC'];
        }

        $memberIds = $this->getMemberDao()->searchMemberIds($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::column($memberIds, 'userId');
    }

    public function searchMultiClassIds($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareConditions($conditions);
        $conditions = array_merge($conditions, ['multiClassId_NE' => 0]);

        if (is_array($sort)) {
            $orderBy = $sort;
        } else {
            $orderBy = ['createdTime' => 'DESC'];
        }

        $members = $this->getMemberDao()->search($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::column($members, 'multiClassId');
    }

    public function findMemberUserIdsByCourseId($courseId)
    {
        return ArrayToolkit::column($this->getMemberDao()->findUserIdsByCourseId($courseId), 'userId');
    }

    public function updateMember($id, $fields)
    {
        // learnedElectiveTaskNum是通过定时任务添加的所以需要做判断
        if (!$this->getMemberDao()->isFieldExist('learnedElectiveTaskNum') && isset($fields['learnedElectiveTaskNum'])) {
            unset($fields['learnedElectiveTaskNum']);
        }

        return $this->getMemberDao()->update($id, $fields);
    }

    public function updateMembers($conditions, $updateFields)
    {
        return $this->getMemberDao()->updateMembers($conditions, $updateFields);
    }

    public function isMemberNonExpired($course, $member)
    {
        if (empty($course) || empty($member)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $vipNonExpired = true;
        if ('vip_join' == $member['joinedChannel']) {
            // 会员加入的情况下
            $vipNonExpired = $this->isVipMemberNonExpired($course, $member);
        }

        if (0 == $member['deadline']) {
            return $vipNonExpired;
        }

        if ($member['deadline'] > time() && $course['expiryStartDate'] < time()) {
            return $vipNonExpired;
        } else {
            return false;
        }
    }

    /**
     * 会员到期后、会员被取消后、课程会员等级被提高均为过期
     *
     * @param  $course
     * @param  $member
     *
     * @return bool 会员加入的学员是否已到期
     */
    protected function isVipMemberNonExpired($course, $member)
    {
        $vipApp = $this->getAppService()->getAppByCode('vip');

        if (empty($vipApp)) {
            return false;
        }

        if (!empty($member['classroomId']) && 'classroom' == $member['joinedType']) {
            $classroom = $this->getClassroomService()->getClassroom($member['classroomId']);
            $status = $this->getVipService()->checkUserVipRight($member['userId'], 'classroom', $classroom['id']);
        } else {
            $status = $this->getVipService()->checkUserVipRight($member['userId'], 'course', $course['id']);
        }

        return 'ok' === $status;
    }

    //TODO 有问题 分页数无效
    public function findCourseStudents($courseId, $start, $limit)
    {
        return $this->getMemberDao()->findByCourseIdAndRole($courseId, 'student');
    }

    public function findCourseStudentsByCourseIds($courseIds)
    {
        return $this->getMemberDao()->findByCourseIds($courseIds);
    }

    public function findLatestStudentsByCourseSetId($courseSetId, $offset, $limit)
    {
        $result = $this->getMemberDao()->findByConditionsGroupByUserId(
            [
                'role' => 'student',
                'courseSetId' => $courseSetId,
                'locked' => 0,
            ],
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );

        $memberIds = array_column($result, 'id');

        $members = $this->getMemberDao()->findByIds($memberIds);
        $members = ArrayToolkit::index($members, 'id');

        $sortedMembers = [];

        foreach ($memberIds as $memberId) {
            $sortedMembers[] = $members[$memberId];
        }

        return $sortedMembers;
    }

    public function getCourseStudentCount($courseId)
    {
        $conditions = [
            'courseId' => $courseId,
            'role' => 'student',
        ];

        return $this->getMemberDao()->count($conditions);
    }

    public function getMultiClassMembers($courseId, $multiClassId, $role)
    {
        return $this->getMemberDao()->getMultiClassMembers($courseId, $multiClassId, $role);
    }

    public function findCourseTeachers($courseId)
    {
        return $this->getMemberDao()->findByCourseIdAndRole($courseId, 'teacher');
    }

    public function findMultiClassMemberByMultiClassIdAndRole($multiClassId, $role)
    {
        return $this->getMemberDao()->findByMultiClassIdAndRole($multiClassId, $role);
    }

    public function findCourseSetTeachers($courseId)
    {
        return $this->getMemberDao()->findByCourseSetIdAndRole($courseId, 'teacher');
    }

    public function findCourseSetTeachersAndAssistant($courseSetId)
    {
        return $this->getMemberDao()->findByCourseSetIdAndRoles($courseSetId, ['teacher', 'assistant']);
    }

    public function findMultiClassMembersByMultiClassIdsAndRole($multiClassIds, $role)
    {
        if (empty($multiClassIds)) {
            return [];
        }

        return $this->getMemberDao()->findByMultiClassIdsAndRole($multiClassIds, $role);
    }

    public function isCourseTeacher($courseId, $userId)
    {
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if (!$member) {
            return false;
        } else {
            return empty($member) || 'teacher' != $member['role'] ? false : true;
        }
    }

    public function isCourseAssistant($courseId, $userId)
    {
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if (!$member) {
            return false;
        } else {
            return empty($member) || 'assistant' != $member['role'] ? false : true;
        }
    }

    public function isCourseStudent($courseId, $userId)
    {
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if (!$member) {
            return false;
        } else {
            return empty($member) || 'student' != $member['role'] ? false : true;
        }
    }

    public function isCourseMember($courseId, $userId)
    {
        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        return empty($member) ? false : true;
    }

    public function setDefaultTeacher($courseId)
    {
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($courseId);

        $teacher = [
            'courseId' => $courseId,
            'courseSetId' => $course['courseSetId'],
            'userId' => $user['id'],
            'role' => 'teacher',
            'isVisible' => 1,
        ];

        $teacher = $this->addMember($teacher);

        $fields = ['teacherIds' => [$user['id']]];
        $this->getCourseDao()->update($courseId, $fields);
        $this->dispatchEvent('course.teacher.create', new Event($course, ['teacher' => $teacher]));
    }

    public function setCourseTeachers($courseId, $teachers, $multiClassId = 0)
    {
        $userIds = ArrayToolkit::column($teachers, 'id');
        $existTeacherMembers = $this->findCourseTeachers($courseId);
        $existTeacherIds = ArrayToolkit::column($existTeacherMembers, 'userId');
        $deleteTeacherIds = array_values(array_diff($existTeacherIds, $userIds));

        $this->dispatchEvent('course.teachers.update.before', new Event($courseId, [
            'teachers' => $teachers,
            'deleteTeacherIds' => $deleteTeacherIds,
        ]));

        $course = $this->getCourseService()->tryManageCourse($courseId);

        if (empty($userIds)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $teacherMembers = $this->buildTeachers($course, $teachers, $existTeacherMembers, $multiClassId);
        if (empty($teacherMembers)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        // 删除老师
        if (!$multiClassId) {
            $this->deleteMemberByCourseIdAndRole($courseId, 'teacher');
        } else {
            $this->deleteMemberByMultiClassIdAndRole($multiClassId, 'teacher');
        }

        // 删除目前还是学员的成员
        $this->getMemberDao()->batchDelete([
            'courseId' => $courseId,
            'userIds' => $userIds,
        ]);

        $this->getMemberDao()->batchCreate($teacherMembers);
        $this->updateCourseTeacherIds($courseId, $teachers);
        $addTeachers = array_values(array_diff($userIds, $existTeacherIds));
        $this->dispatchEvent('course.teachers.update', new Event($course, [
            'teachers' => $teachers,
            'deleteTeachers' => $deleteTeacherIds,
            'addTeachers' => $addTeachers,
        ]));
    }

    public function setCourseAssistants($courseId, $assistantIds, $multiClassId = 0)
    {
        if (empty($assistantIds)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (count($assistantIds) > self::ASSISTANT_LIMIT_NUM) {
            $this->createNewException(MultiClassException::MULTI_CLASS_ASSISTANT_NUMBER_EXCEED());
        }

        $course = $this->getCourseService()->tryManageCourse($courseId);

        $assistantMembers = $this->buildMultiClassAssistant($course, $assistantIds, $multiClassId);

        if (empty($assistantMembers)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!$multiClassId) {
            $this->deleteMemberByCourseIdAndRole($courseId, 'assistant');
        } else {
            $this->deleteMemberByMultiClassIdAndRole($multiClassId, 'assistant');
        }

        $this->getMemberDao()->batchDelete([
            'courseId' => $courseId,
            'userIds' => $assistantIds,
        ]);

        $this->getMemberDao()->batchCreate($assistantMembers);

        $infoData = [
            'assistantIds' => $assistantIds,
        ];

        $this->getLogService()->info(
            'course',
            'set_assistant',
            "设置课程#{$courseId}下助教",
            $infoData
        );
    }

    public function releaseMultiClassMember($courseId, $multiClassId)
    {
        if (empty($courseId) || empty($multiClassId)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $multiClassExisted = $this->getMultiClassService()->getMultiClass($multiClassId);

        if ($courseId != $multiClassExisted['courseId']) {
            throw MultiClassException::MULTI_CLASS_COURSE_NOT_MATCH();
        }

        $conditions = [
            'courseId' => $courseId,
            'multiClassId' => $multiClassId,
        ];

        $this->getMemberDao()->updateMembers(array_merge($conditions, ['role' => 'teacher']), ['multiClassId' => 0]);

        $this->getMemberDao()->batchDelete(array_merge($conditions, ['role' => 'assistant']));

        $this->getLogService()->info(
            'course',
            'release_multi_class_member',
            "释放班课#{$multiClassId}下成员关系"
        );
    }

    private function updateCourseTeacherIds($courseId, $teachers)
    {
        $teachers = ArrayToolkit::group($teachers, 'isVisible');

        $visibleTeacherIds = empty($teachers[1]) ? [] : ArrayToolkit::column($teachers[1], 'id');
        $fields = ['teacherIds' => array_unique($visibleTeacherIds)];
        $course = $this->getCourseDao()->update($courseId, $fields);
    }

    private function buildTeachers($course, $teachers, $existTeachers, $multiClassId)
    {
        $teacherMembers = [];
        $teachers = ArrayToolkit::index($teachers, 'id');
        $users = $this->getUserService()->findUsersByIds(array_keys($teachers));
        $existTeachers = ArrayToolkit::index($existTeachers, 'userId');
        $seq = 0;
        foreach ($teachers as $index => $teacher) {
            $user = $users[$teacher['id']];
            if (in_array('ROLE_TEACHER', $user['roles']) || $course['creator'] == $user['id']) {
                if (empty($multiClassId)) {
                    $teacherMultiClassId = empty($existTeachers[$teacher['id']]) ? 0 : $existTeachers[$teacher['id']]['multiClassId'];
                } else {
                    $teacherMultiClassId = $multiClassId;
                }

                $teacherMembers[] = [
                    'multiClassId' => $teacherMultiClassId,
                    'courseId' => $course['id'],
                    'courseSetId' => $course['courseSetId'],
                    'userId' => $teacher['id'],
                    'role' => 'teacher',
                    'seq' => $seq++,
                    'isVisible' => empty($teacher['isVisible']) ? 0 : 1,
                ];
            }
        }

        return $teacherMembers;
    }

    private function buildMultiClassAssistant($course, $assistantIds, $multiClassId)
    {
        $assistantMembers = [];
        $users = $this->getUserService()->findUsersByIds($assistantIds);
        $seq = 0;
        foreach ($assistantIds as $assistantId) {
            $user = $users[$assistantId];
            if (in_array('ROLE_TEACHER_ASSISTANT', $user['roles'])) {
                $assistantMembers[] = [
                    'multiClassId' => $multiClassId,
                    'courseId' => $course['id'],
                    'courseSetId' => $course['courseSetId'],
                    'userId' => $assistantId,
                    'role' => 'assistant',
                    'seq' => $seq++,
                    'isVisible' => 1,
                ];
            }
        }

        return $assistantMembers;
    }

    /**
     * //这个方法应该是取消教师角色的时候退出课程用到的
     *
     * @todo 当用户拥有大量的教学计划老师角色时，这个方法效率是有就有问题咯！鉴于短期内用户不会拥有大量的教学计划老师角色，先这么做着。
     */
    public function cancelTeacherInAllCourses($userId)
    {
        $members = $this->getMemberDao()->findByUserIdAndRole($userId, 'teacher');

        foreach ($members as $member) {
            $course = $this->getCourseService()->getCourse($member['courseId']);

            $this->removeMember($member);

            $fields = [
                'teacherIds' => array_diff($course['teacherIds'], [$member['userId']]),
            ];
            $this->getCourseDao()->update($member['courseId'], $fields);
        }

        $this->getLogService()->info('course', 'cancel_teachers_all', "取消用户#{$userId}所有的教学计划老师角色");
    }

    public function remarkStudent($courseId, $userId, $remark)
    {
        $member = $this->getCourseMember($courseId, $userId);

        if (empty($member)) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }

        $fields = ['remark' => empty($remark) ? '' : (string)$remark];

        return $this->getMemberDao()->update($member['id'], $fields);
    }

    public function deleteMemberByCourseIdAndRole($courseId, $role)
    {
        return $this->getMemberDao()->deleteByCourseIdAndRole($courseId, $role);
    }

    public function deleteMemberByMultiClassIdAndRole($multiClassId, $role)
    {
        return $this->getMemberDao()->deleteByMultiClassAndRole($multiClassId, $role);
    }

    public function deleteMemberByCourseId($courseId)
    {
        return $this->getMemberDao()->deleteByCourseId($courseId);
    }

    public function findMembersByUserIdAndJoinType($userId, $joinedType = 'course')
    {
        $courseIds = $this->getMemberDao()->findByUserIdAndJoinType($userId, $joinedType);

        return ArrayToolkit::column($courseIds, 'courseId');
    }

    public function quitCourseByDeadlineReach($userId, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ('student' != $member['role'])) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }

        $isNonExpired = $this->isMemberNonExpired($course, $member);

        if ($isNonExpired) {
            $this->createNewException(MemberException::NON_EXPIRED_MEMBER());
        }
        $user = $this->getUserService()->getUser($userId);

        $this->removeMember($member, [
            'reason' => 'course.member.operation.quit_deadline_reach',
            'reason_type' => 'system',
        ]);

        $this->dispatchEvent(
            'course.quit',
            $course,
            ['userId' => $userId, 'member' => $member]
        );

        $this->getCourseDao()->update(
            $courseId,
            [
                'studentNum' => $this->getCourseStudentCount($courseId),
            ]
        );

        $infoData = [
            'courseId' => $course['id'],
            'title' => CourseTitleUtils::getDisplayedTitle($course),
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
        ];

        $this->getLogService()->info(
            'course',
            'remove_student',
            "教学计划《{$course['title']}》(#{$course['id']})，学员({$user['nickname']})因达到有效期退出教学计划(#{$member['id']})",
            $infoData
        );
    }

    public function becomeStudent($courseId, $userId, $info = [])
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        if (!in_array($course['status'], ['published'])) {
            $this->createNewException(CourseException::UNPUBLISHED_COURSE());
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $user = $this->getUserService()->getUserByUUID($userId);
            if(empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        }

        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if ($member) {
            if ('teacher' == $member['role']) {
                return $member;
            } else {
                $this->createNewException(MemberException::DUPLICATE_MEMBER());
            }
        }

        //按照教学计划有效期模式计算学员有效期
        $deadline = 0;
        $expiryMode = $info['expiryMode'] ?? $course['expiryMode'];
        $expiryDays = $info['expiryDays'] ?? $course['expiryDays'];
        if ('days' == $expiryMode && $expiryDays > 0) {
            $endTime = strtotime(date('Y-m-d', time()) . ' 23:59:59'); //系统当前时间
            $deadline = $expiryDays * 24 * 60 * 60 + $endTime;
        } elseif ('date' == $expiryMode || 'end_date' == $expiryMode) {
            $deadline = $course['expiryEndDate'];
        }

        if (!empty($info['orderId'])) {
            $order = $this->getOrderService()->getOrder($info['orderId']);

            if (empty($order)) {
                $this->createNewException(OrderException::NOTFOUND_ORDER());
            }
        } else {
            $order = null;
        }

        $reason = $this->buildJoinReason($info, $order);

        $fields = [
            'courseId' => $courseId,
            'userId' => $userId,
            'courseSetId' => $course['courseSetId'],
            'orderId' => empty($order) ? 0 : $order['id'],
            'deadline' => $deadline,
            'joinedChannel' => $reason['reason_type'],
            'role' => 'student',
            'remark' => empty($info['remark']) ? '' : $info['remark'],
            'createdTime' => time(),
            'refundDeadline' => $this->getRefundDeadline(),
        ];
        $member = $this->addMember($fields, $reason);

        $this->refreshMemberNoteNumber($courseId, $userId);

        $this->dispatchEvent(
            'course.join',
            $course,
            ['userId' => $member['userId'], 'member' => $member]
        );

        return $member;
    }

    private function buildJoinReason($info, $order)
    {
        if (ArrayToolkit::requireds($info, ['reason', 'reason_type'])) {
            return ArrayToolkit::parts($info, ['reason', 'reason_type']);
        }

        $orderId = empty($order) ? 0 : $order['id'];

        return $this->getMemberOperationService()->getJoinReasonByOrderId($orderId);
    }

    private function getRefundDeadline()
    {
        $refundSetting = $this->getSettingService()->get('refund');
        if (empty($refundSetting['maxRefundDays'])) {
            return 0;
        }

        return time() + $refundSetting['maxRefundDays'] * 24 * 60 * 60;
    }

    public function batchBecomeStudents($courseId, $memberIds, $classroomId = 0)
    {
        if (empty($memberIds)) {
            return [];
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        if (!in_array($course['status'], ['published'])) {
            $this->createNewException(CourseException::UNPUBLISHED_COURSE());
        }

        $users = $this->getUserService()->findUsersByIds($memberIds);
        $userIds = ArrayToolkit::column($users, 'id');

        if (empty($users)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $existMembers = $this->searchMembers(['courseId' => $course['id']], [], 0, PHP_INT_MAX);
        $existMemberIds = ArrayToolkit::column($existMembers, 'userId');
        $beAddUserIds = array_diff($userIds, $existMemberIds);

        $membersTaskResults = $this->getTaskResult()->searchTaskResults(
            ['courseId' => $course['id'], 'status' => 'finish'],
            [],
            0,
            PHP_INT_MAX
        );
        $membersTaskResults = ArrayToolkit::group($membersTaskResults, 'userId');

        $courseNotes = $this->getCourseNoteService()->searchNotes(
            [$course['id']],
            ['updatedTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );
        $membersNotes = ArrayToolkit::group($courseNotes, 'userId');

        $classroomMembers = [];
        if ($classroomId > 0) {
            $classroomMembers = $this->getClassroomService()->findClassroomStudents($classroomId, 0, PHP_INT_MAX);
            $classroomMembers = ArrayToolkit::index($classroomMembers, 'userId');
        }

        $newMembers = [];
        foreach ($beAddUserIds as $userId) {
            $member = [
                'courseId' => $course['id'],
                'userId' => $userId,
                'courseSetId' => $course['courseSetId'],
                'orderId' => 0,
                'role' => 'student',
                'learnedNum' => 0,
                'noteNum' => 0,
                'noteLastUpdateTime' => 0,
                'createdTime' => time(),
                'joinedType' => $classroomId > 0 ? 'classroom' : 'course',
            ];

            if ($classroomId > 0 && !empty($classroomMembers[$userId])) {
                $member['classroomId'] = $classroomId;
                $member['deadline'] = $classroomMembers[$userId]['deadline'];
                $member['joinedChannel'] = $classroomMembers[$userId]['joinedChannel'];
            } else {
                $member['deadline'] = $this->getMemberDeadline($course);
            }

            if (!empty($membersNotes[$userId])) {
                $member['noteNum'] = count($membersNotes[$userId]);
                $member['noteLastUpdateTime'] = $membersNotes[$userId][0]['updatedTime'];
            }

            if (!empty($membersTaskResults[$userId])) {
                $member['learnedNum'] = count($membersTaskResults[$userId]);
            }

            $newMembers[] = $member;
        }

        $this->getMemberDao()->batchCreate($newMembers);
        $newMembers = $this->searchMembers(['courseId' => $course['id']], [], 0, PHP_INT_MAX);

        $this->getCourseService()->updateCourseStatistics($course['id'], ['studentNum']);
        $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], ['studentNum']);
        $this->dispatchEvent('course.batch.join', new Event($newMembers));

        return $newMembers;
    }

    protected function getMemberDeadline($course)
    {
        //按照教学计划有效期模式计算学员有效期
        $deadline = 0;
        if ('days' == $course['expiryMode'] && $course['expiryDays'] > 0) {
            $endTime = strtotime(date('Y-m-d', time())); //从第二天零点开始计算
            $deadline = $course['expiryDays'] * 24 * 60 * 60 + $endTime;
        } elseif ('date' == $course['expiryMode'] || 'end_date' == $course['expiryMode']) {
            $deadline = $course['expiryEndDate'];
        }

        return $deadline;
    }

    public function removeStudent($courseId, $userId, $reason = [])
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ('student' != $member['role'])) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }

        $reason = ArrayToolkit::parts($reason, ['reason', 'reason_type']);

        $this->removeMember($member, $reason);

        $this->getCourseDao()->update(
            $courseId,
            [
                'studentNum' => $this->getCourseStudentCount($courseId),
            ]
        );

        $removeMember = $this->getUserService()->getUser($member['userId']);

        $infoData = [
            'courseId' => $course['id'],
            'title' => CourseTitleUtils::getDisplayedTitle($course),
            'userId' => $removeMember['id'],
            'nickname' => $removeMember['nickname'],
        ];

        if (isset($reason['reason_type']) && ('exit' === $reason['reason_type'] || 'classroom_exit' === $reason['reason_type'])) {
            $this->getLogService()->info(
                'course',
                'exit_course',
                "学员({$removeMember['nickname']})(#{$member['id']})退出了教学计划《{$course['title']}》(#{$course['id']})",
                $infoData
            );
        } else {
            $this->getLogService()->info(
                'course',
                'remove_student',
                "教学计划《{$course['title']}》(#{$course['id']})，移除学员({$removeMember['nickname']})(#{$member['id']})",
                $infoData
            );
        }

        $this->dispatchEvent(
            'course.quit',
            $course,
            ['userId' => $member['userId'], 'member' => $member]
        );
    }

    public function lockStudent($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);
        if (empty($member)) {
            return;
        }

        if ('student' != $member['role']) {
            $this->createNewException(MemberException::MEMBER_NOT_STUDENT());
        }

        if ($member['locked']) {
            return;
        }

        $this->getMemberDao()->update($member['id'], ['locked' => 1]);
    }

    public function unlockStudent($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if (empty($member)) {
            return;
        }

        if ('student' != $member['role']) {
            $this->createNewException(MemberException::MEMBER_NOT_STUDENT());
        }

        if (empty($member['locked'])) {
            return;
        }

        $this->getMemberDao()->update($member['id'], ['locked' => 0]);
    }

    public function createMemberByClassroomJoined($courseId, $userId, $classRoomId, array $info = [])
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $deadline = 0;
        if (isset($info['deadline'])) {
            $deadline = $info['deadline'];
        } elseif ('days' == $course['expiryMode']) {
            if (!empty($course['expiryDays'])) {
                $deadline = strtotime('+' . $course['expiryDays'] . ' days');
            }
        } elseif (!empty($course['expiryEndDate'])) {
            $deadline = $course['expiryEndDate'];
        }

        $learnedNum = $this->getTaskResultService()->countTaskResults(
            ['courseId' => $courseId, 'userId' => $userId, 'status' => 'finish']
        );
        $lastLearnTime = ($learnedNum > 0) ? time() : 0;

        $learnedCompulsoryTaskNum = $this->getTaskResultService()->countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $courseId);

        $fields = [
            'courseId' => $courseId,
            'courseSetId' => $course['courseSetId'],
            'userId' => $userId,
            'orderId' => empty($info['orderId']) ? 0 : $info['orderId'],
            'deadline' => $deadline,
            'joinedChannel' => empty($info['joinedChannel']) ? '' : $info['joinedChannel'],
            'role' => 'student',
            'remark' => empty($info['orderNote']) ? '' : $info['orderNote'],
            'createdTime' => time(),
            'classroomId' => $classRoomId,
            'joinedType' => 'classroom',
            'learnedNum' => $learnedNum,
            'learnedCompulsoryTaskNum' => $learnedCompulsoryTaskNum,
            'lastLearnTime' => $lastLearnTime,
        ];
        $isMember = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if ($isMember) {
            return [];
        }

        $member = $this->addMember(
            $fields,
            [
                'reason' => 'course.member.operation.reason.join_classroom',
                'reason_type' => 'classroom_join',
            ]
        );
        $fields = [
            'studentNum' => $this->getCourseStudentCount($courseId),
        ];
        $this->getCourseDao()->update($courseId, $fields);
        $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], ['studentNum']);

        $this->dispatchEvent('classroom.course.join', new Event($course, ['member' => $member]));

        return $member;
    }

    public function batchCreateMembers($members)
    {
        if (empty($members)) {
            return;
        }

        $this->getMemberDao()->batchCreate($members);

        return true;
    }

    /**
     * @param $userId
     * @param $courseIds
     *
     * @return array
     *
     * @deprecated 名称上不合适，返回值为courseMembers 但是命名为返回Course，替代函数为@findCourseMembersByUserIdAndCourseIds
     */
    public function findCoursesByStudentIdAndCourseIds($userId, $courseIds)
    {
        if (empty($courseIds) || 0 === count($courseIds)) {
            return [];
        }

        return $this->getMemberDao()->findByUserIdAndCourseIds($userId, $courseIds);
    }

    public function findCourseMembersByUserIdAndCourseIds($userId, $courseIds)
    {
        if (empty($courseIds) || 0 === count($courseIds)) {
            return [];
        }

        return $this->getMemberDao()->findByUserIdAndCourseIds($userId, $courseIds);
    }

    public function findCourseMembersByUserIdAndClassroomId($userId, $classroomId)
    {
        return $this->getMemberDao()->findByUserIdAndClassroomId($userId, $classroomId);
    }

    public function findCourseMembersByUserIdsAndClassroomId($userIds, $classroomId)
    {
        return $this->getMemberDao()->findByUserIdsAndClassroomId($userIds, $classroomId);
    }

    public function findMembersByUserIdsAndRole($userIds, $role)
    {
        return $this->getMemberDao()->findByUserIdsAndRole($userIds, $role);
    }

    public function becomeStudentByClassroomJoined($courseId, $userId)
    {
        $isCourseStudent = $this->isCourseStudent($courseId, $userId);
        $classroom = $this->getClassroomService()->getClassroomByCourseId($courseId);

        if (!empty($classroom)) {
            $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $userId);

            if (!$isCourseStudent && !empty($member) && array_intersect($member['role'],
                    ['student', 'teacher', 'headTeacher', 'assistant'])
            ) {
                $info = ArrayToolkit::parts($member, ['joinedChannel']);
                $member = $this->createMemberByClassroomJoined($courseId, $userId, $member['classroomId'], $info);

                return $member;
            }
        }

        return [];
    }

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['date'])) {
            $dates = [
                'yesterday' => [
                    strtotime('yesterday'),
                    strtotime('today'),
                ],
                'today' => [
                    strtotime('today'),
                    strtotime('tomorrow'),
                ],
                'this_week' => [
                    strtotime('Monday this week'),
                    strtotime('Monday next week'),
                ],
                'last_week' => [
                    strtotime('Monday last week'),
                    strtotime('Monday this week'),
                ],
                'next_week' => [
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week')),
                ],
                'this_month' => [
                    strtotime('first day of this month midnight'),
                    strtotime('first day of next month midnight'),
                ],
                'last_month' => [
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight'),
                ],
                'next_month' => [
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
                ],
            ];

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
                $conditions['startTimeLessThan'] = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }

        if (isset($conditions['creator']) && !empty($conditions['creator'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['creator']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['creator']);
        }

        if (isset($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    public function refreshMemberNoteNumber($courseId, $userId)
    {
        $member = $this->getCourseMember($courseId, $userId);

        if (empty($member)) {
            return false;
        }

        $number = $this->getCourseNoteService()->countNotesByUserIdAndCourseId($userId, $courseId);

        $this->getMemberDao()->update(
            $member['id'],
            [
                'noteNum' => (int)$number,
                'noteLastUpdateTime' => time(),
            ]
        );

        return true;
    }

    public function findTeacherMembersByUserId($userId)
    {
        return $this->getMemberDao()->findByUserIdAndRole($userId, 'teacher');
    }

    /**
     * @param  $userId
     * @param  $courseSetId
     *
     * @return array
     */
    public function findTeacherMembersByUserIdAndCourseSetId($userId, $courseSetId)
    {
        return $this->getMemberDao()->findByUserIdAndCourseSetIdAndRole($userId, $courseSetId, 'teacher');
    }

    public function findLastLearnTimeRecordStudents($userIds)
    {
        if (empty($userIds)) {
            return [];
        }

        return $this->getMemberDao()->findLastLearnTimeRecordStudents($userIds);
    }

    /**
     * @param int $userId
     *
     * @return mixed
     */
    public function findStudentMemberByUserId($userId)
    {
        return $this->getMemberDao()->findByUserIdAndRole($userId, 'student');
    }

    public function countQuestionsByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->countThreadsByCourseIdAndUserId($courseId, $userId, 'question');
    }

    public function countActivitiesByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->countActivitiesByCourseIdAndUserId($courseId, $userId);
    }

    public function countDiscussionsByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->countThreadsByCourseIdAndUserId($courseId, $userId, 'discussion');
    }

    public function countPostsByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->countPostsByCourseIdAndUserId($courseId, $userId);
    }

    public function searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit)
    {
        return $this->getMemberDao()->searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit);
    }

    public function batchUpdateMemberDeadlinesByDay($courseId, $userIds, $day, $waveType = 'plus')
    {
        $this->getCourseService()->tryManageCourse($courseId);
        if ($this->checkDayAndWaveTypeForUpdateDeadline($courseId, $userIds, $day, $waveType)) {
            foreach ($userIds as $userId) {
                $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

                $member['deadline'] = $member['deadline'] > 0 ? $member['deadline'] : time();
                $deadline = 'plus' == $waveType ? $member['deadline'] + $day * 24 * 60 * 60 : $member['deadline'] - $day * 24 * 60 * 60;

                $this->getMemberDao()->update(
                    $member['id'],
                    [
                        'deadline' => $deadline,
                    ]
                );
            }
        }
    }

    public function checkDayAndWaveTypeForUpdateDeadline($courseId, $userIds, $day, $waveType = 'plus')
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if ('forever' == $course['expiryMode']) {
            return false;
        }
        $members = $this->searchMembers(
            ['userIds' => $userIds, 'courseId' => $courseId],
            ['deadline' => 'ASC'],
            0,
            PHP_INT_MAX
        );
        if ('minus' == $waveType) {
            $member = array_shift($members);
            $maxAllowMinusDay = intval(($member['deadline'] - time()) / (24 * 3600));
            if ($day > $maxAllowMinusDay) {
                return false;
            }
        }

        return true;
    }

    public function batchUpdateMemberDeadlinesByDate($courseId, $userIds, $date)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $date = TimeMachine::isTimestamp($date) ? $date : strtotime($date . ' 23:59:59');
        if ($this->checkDeadlineForUpdateDeadline($date)) {
            foreach ($userIds as $userId) {
                $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);
                $this->getMemberDao()->update(
                    $member['id'],
                    [
                        'deadline' => $date,
                    ]
                );
            }
        }
    }

    public function checkDeadlineForUpdateDeadline($date)
    {
        return $date > time();
    }

    public function updateMemberDeadlineByClassroomIdAndUserId($classroomId, $userId, $deadline)
    {
        if (empty($classroomId) || empty($userId)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getMemberDao()->updateByClassroomIdAndUserId($classroomId, $userId, [
            'deadline' => $deadline,
        ]);
    }

    public function updateMembersDeadlineByClassroomId($classroomId, $deadline)
    {
        return $this->getMemberDao()->updateByClassroomId($classroomId, ['deadline' => $deadline]);
    }

    public function findMembersByCourseIdAndRole($courseId, $role)
    {
        return $this->getMemberDao()->findByCourseIdAndRole($courseId, $role);
    }

    public function findDailyIncreaseNumByCourseIdAndRoleAndTimeRange($courseId, $role, $timeRange = [], $format = '%Y-%m-%d')
    {
        $conditions = [
            'courseId' => $courseId,
            'role' => $role,
        ];
        if (!empty($timeRange)) {
            $conditions['startTimeGreaterThan'] = strtotime($timeRange['startDate']);
            $conditions['startTimeLessThan'] = empty($timeRange['endDate']) ? time() : strtotime($timeRange['endDate'] . '+1 day');
        }

        return $this->getMemberDao()->searchMemberCountsByConditionsGroupByCreatedTimeWithFormat($conditions, $format);
    }

    public function findMembersByIds($ids)
    {
        return $this->getMemberDao()->findByIds($ids);
    }

    public function countStudentMemberByCourseSetId($courseSetId)
    {
        $conditions = [
            'courseSetId' => $courseSetId,
            'role' => 'student',
        ];

        return $this->getMemberDao()->count($conditions);
    }

    public function recountLearningDataByCourseId($courseId)
    {
        $this->refreshCourseMembersFinishData($courseId);
    }

    public function refreshMemberFinishData($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $member = $this->getCourseMember($course['id'], $userId);
        if (empty($course['compulsoryTaskNum'])) {
            $isFinished = true;
        } else {
            $isFinished = (int)($member['learnedCompulsoryTaskNum'] / $course['compulsoryTaskNum']) >= 1;
        }
        $finishTime = $isFinished ? time() : 0;
        $this->updateMembers(
            ['courseId' => $course['id'], 'userId' => $userId],
            ['lastLearnTime' => time(), 'finishedTime' => $finishTime, 'isLearned' => $isFinished ? 1 : 0]
        );
        if ($isFinished) {
            $this->dispatchEvent('course_member.finished', new Event($member, ['course' => $course]));
        }
    }

    public function refreshCourseMembersFinishData($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            return;
        }
        $members = ArrayToolkit::index(
            $this->searchMembers(['courseId' => $courseId, 'role' => 'student'], [], 0, PHP_INT_MAX, ['id', 'userId', 'lastLearnTime']),
            'userId'
        );
        if (empty($members)) {
            return;
        }
        $updateMembers = [];
        $finishedTaskNums = $this->getTaskResultService()->countTaskNumGroupByUserId(['status' => 'finish', 'courseId' => $courseId]);
        $finishedCompulsoryTaskNums = $this->getTaskResultService()->countFinishedCompulsoryTaskNumGroupByUserId($courseId);
        // learnedElectiveTaskNum是通过定时任务添加的所以需要做判断
        $isFieldExist = false;
        if ($this->getMemberDao()->isFieldExist('learnedElectiveTaskNum')) {
            $isFieldExist = true;
        }
        foreach ($members as $member) {
            $learnedNum = empty($finishedTaskNums[$member['userId']]) ? 0 : $finishedTaskNums[$member['userId']]['count'];
            $learnedCompulsoryTaskNum = empty($finishedCompulsoryTaskNums[$member['userId']]) ? 0 : $finishedCompulsoryTaskNums[$member['userId']]['count'];
            $updateMember = [
                'id' => $member['id'],
                'learnedNum' => $learnedNum,
                'learnedCompulsoryTaskNum' => $learnedCompulsoryTaskNum,
                'isLearned' => $course['compulsoryTaskNum'] > 0 && $course['compulsoryTaskNum'] <= $learnedCompulsoryTaskNum ? '1' : '0',
                'finishedTime' => $course['compulsoryTaskNum'] > 0 && $course['compulsoryTaskNum'] <= $learnedCompulsoryTaskNum ? $member['lastLearnTime'] : 0,
            ];
            // learnedElectiveTaskNum是通过定时任务添加的所以需要做判断
            if ($isFieldExist) {
                $updateMember['learnedElectiveTaskNum'] = $learnedNum - $learnedCompulsoryTaskNum;
            }
            $updateMembers[] = $updateMember;
        }
        $this->getMemberDao()->batchUpdate(ArrayToolkit::column($updateMembers, 'id'), $updateMembers);
        $this->dispatchEvent('course.members.finish_data_refresh', new Event($course, ['updatedMembers' => $updateMembers]));
    }

    protected function createOrder($goodsSpecsId, $userId, $data)
    {
        $courseProduct = $this->getOrderFacadeService()->getOrderProduct('course', ['targetId' => $goodsSpecsId]);

        $params = [
            'created_reason' => $data['remark'],
            'source' => $data['source'],
            'create_extra' => $data,
            'deducts' => empty($data['deducts']) ? [] : $data['deducts'],
        ];

        return $this->getOrderFacadeService()->createSpecialOrder($courseProduct, $userId, $params);
    }

    protected function createOperateRecord($member, $operateType, $reason)
    {
        $currentUser = $this->getCurrentUser();
        $data['member'] = $member;
        $course = $this->getCourseService()->getCourse($member['courseId']);
        $record = [
            'user_id' => $member['userId'],
            'member_id' => $member['id'],
            'member_type' => $member['role'],
            'target_id' => $member['courseId'],
            'target_type' => 'course',
            'operate_type' => $operateType,
            'operate_time' => time(),
            'operator_id' => $currentUser['id'],
            'data' => $data,
            'order_id' => $member['orderId'],
            'title' => $course['title'],
            'course_set_id' => $course['courseSetId'],
            'parent_id' => $course['parentId'],
        ];
        $otherMemberCount = $this->countMembers([
            'excludeIds' => [$member['id']],
            'courseSetId' => $member['courseSetId'],
            'userId' => $member['userId'],
            'role' => 'student',
        ]);

        if (empty($otherMemberCount)) {
            'join' == $operateType ? $record['join_course_set'] = 1 : $record['exit_course_set'] = 1;
        }

        $record = array_merge($record, $reason);
        $record = $this->getMemberOperationService()->createRecord($record);

        return $record;
    }

    private function addMember($member, $reason = [])
    {
        try {
            $this->beginTransaction();
            $member = $this->getMemberDao()->create(array_merge($member, $this->getMemberHistoryData($member['userId'], $member['courseId'])));

            $multiClass = $this->getMultiClassService()->getMultiClassByCourseId($member['courseId']);
            if (!empty($multiClass)) {
                if ('group' == $multiClass['type']) {
                    $this->getMultiClassGroupService()->setGroupNewStudent($multiClass, $member['userId']);
                } else {
                    $this->getAssistantStudentService()->setAssistantStudents($multiClass['courseId'], $multiClass['id']);
                }
                $this->getMultiClassRecordService()->createRecord($member['userId'], $multiClass['id']);
            }

            if (!empty($reason)) {
                $this->createOperateRecord($member, 'join', $reason);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $member;
    }

    protected function getMemberHistoryData($userId, $courseId)
    {
        $course = $this->getCourseDao()->get($courseId);
        $recordCount = $this->getMemberOperationService()->countRecords([
            'user_id' => $userId,
            'target_type' => 'course',
            'target_id' => $courseId,
            'operate_type' => 'join',
        ]);

        if (empty($recordCount) || empty($course)) {
            return [];
        }

        $learnedNum = $this->getTaskResultService()->countTaskResults(
            ['courseId' => $courseId, 'userId' => $userId, 'status' => 'finish']
        );
        $learnedCompulsoryTaskNum = $this->getTaskResultService()->countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $courseId);
        $courseMemberConditions = ['courseId' => $courseId, 'userId' => $userId];
        $lastLearnTaskResult = $this->getTaskResultService()->searchTaskResults($courseMemberConditions, ['updatedTime' => 'DESC'], 0, 1, ['updatedTime']);
        $firstLearnTaskResult = $this->getTaskResultService()->searchTaskResults($courseMemberConditions, ['createdTime' => 'ASC'], 0, 1, ['createdTime']);
        $lastFinishedTaskResult = $this->getTaskResultService()->searchTaskResults($courseMemberConditions, ['finishedTime' => 'DESC'], 0, 1, ['finishedTime']);

        return [
            'noteNum' => $this->getCourseNoteService()->countCourseNotes($courseMemberConditions),
            'isLearned' => $course['compulsoryTaskNum'] - $learnedCompulsoryTaskNum > 0 ? 0 : 1,
            'startLearnTime' => empty($firstLearnTaskResult) ? 0 : $firstLearnTaskResult[0]['createdTime'],
            'finishedTime' => empty($lastFinishedTaskResult) ? 0 : $lastFinishedTaskResult[0]['finishedTime'],
            'learnedNum' => $learnedNum,
            'learnedCompulsoryTaskNum' => $learnedCompulsoryTaskNum,
            'learnedElectiveTaskNum' => $learnedNum - $learnedCompulsoryTaskNum ? $learnedNum - $learnedCompulsoryTaskNum : 0,
            'lastLearnTime' => empty($lastLearnTaskResult) ? 0 : $lastLearnTaskResult[0]['updatedTime'],
        ];
    }

    private function removeMember($member, $reason = [])
    {
        try {
            $this->beginTransaction();
            $result = $this->getMemberDao()->delete($member['id']);
            $assistantStudent = $this->getAssistantStudentService()->getByStudentIdAndCourseId($member['userId'], $member['courseId']);
            if (!empty($assistantStudent['group_id'])) {
                $multiClassGroup = $this->getMultiClassGroupService()->getMultiClassGroup($assistantStudent['group_id']);
                if ($multiClassGroup['student_num'] <= 1) {
                    $this->getMultiClassGroupService()->deleteMultiClassGroup($multiClassGroup['id']);
                } else {
                    $this->getMultiClassGroupService()->updateMultiClassGroup($multiClassGroup['id'], ['student_num' => $multiClassGroup['student_num'] - 1]);
                }
            }
            $this->getAssistantStudentService()->delete($assistantStudent['id']);

            if (!empty($reason)) {
                $this->createOperateRecord($member, 'exit', $reason);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $result;
    }

    public function validateSourceCourseStatus($courseId)
    {
        try {
            $this->getCourseProductService()->checkCourseStatus($courseId);
        } catch (\Exception $e) {
            $this->createNewException(CourseException::SOURCE_COURSE_CLOSED_JOIN_DENIED());
        }
    }

    public function getUserLiveroomRoleByCourseIdAndUserId($courseId, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if ($this->isCourseTeacher($courseId, $userId) || $this->isCourseAssistant($courseId, $userId) || in_array('ROLE_EDUCATIONAL_ADMIN', $user['roles'])) {
            $course = $this->getCourseService()->getCourse($courseId);
            $teacherId = array_shift($course['teacherIds']);

            if ($teacherId == $userId) {
                return 'teacher';
            } else {
                return 'speaker';
            }
        }

        return 'student';
    }

    public function findMembersByUserIdAndRoles($userId, $roles)
    {
        return $this->getMemberDao()->findByUserIdAndRoles($userId, $roles);
    }

    public function findGroupUserIdsByCourseIdAndRoles($courseId, $roles)
    {
        $courseMembers = $this->getMemberDao()->findUserIdsByCourseIdAndRoles($courseId, $roles);

        return ArrayToolkit::group($courseMembers, 'role');
    }

    public function getMemberByMultiClassIdAndUserId($multiClassId, $userId)
    {
        return $this->getMemberDao()->getByMultiClassIdAndUserId($multiClassId, $userId);
    }

    public function findMultiClassIdsByUserId($userId)
    {
        return $this->getMemberDao()->findMultiClassIdsByUserId($userId);
    }

    public function countGroupByCourseId($conditions)
    {
        return $this->getMemberDao()->countGroupByCourseId($conditions);
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return MultiClassGroupService
     */
    private function getMultiClassGroupService()
    {
        return $this->createService('MultiClass:MultiClassGroupService');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return OrderRefundService
     */
    protected function getOrderRefundService()
    {
        return $this->createService('Order:OrderRefundService');
    }

    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    /**
     * @return VipRightService
     */
    protected function getVipRightService()
    {
        return $this->createService('VipPlugin:Marketing:VipRightService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->createService('Assistant:AssistantStudentService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResult()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->biz->service('Taxonomy:CategoryService');
    }

    protected function getMemberOperationService()
    {
        return $this->biz->service('MemberOperation:MemberOperationService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }

    /*S2B2C-CUSTOM*/

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }

    /*END*/

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }

    /**
     * @return MultiClassRecordService
     */
    protected function getMultiClassRecordService()
    {
        return $this->createService('MultiClass:MultiClassRecordService');
    }
}
