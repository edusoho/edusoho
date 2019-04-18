<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Dao\CourseDao;
use Biz\Course\MemberException;
use Biz\Order\OrderException;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\LogService;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Course\Service\CourseNoteService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Order\Service\OrderRefundService;
use Codeages\Biz\Order\Service\OrderService;
use VipPlugin\Biz\Vip\Service\VipService;
use Biz\Classroom\Service\ClassroomService;
use AppBundle\Common\TimeMachine;
use Biz\Course\Util\CourseTitleUtils;

/**
 * Class MemberServiceImpl
 * 所有api 均迁移自 courseService 中的对member操作的api.
 */
class MemberServiceImpl extends BaseService implements MemberService
{
    public function becomeStudentAndCreateOrder($userId, $courseId, $data)
    {
        //        $data = ArrayToolkit::parts($data, array('price', 'amount', 'remark', 'isAdminAdded', 'source'));

        if (!ArrayToolkit::requireds($data, array('price', 'remark'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $this->getCourseService()->tryManageCourse($courseId);

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        if ($this->isCourseStudent($course['id'], $user['id'])) {
            $this->createNewException(MemberException::DUPLICATE_MEMBER());
        }

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
                $order = $this->createOrder($course['id'], $user['id'], $data);
            } else {
                $order = array('id' => 0);
                $info = array(
                    'orderId' => $order['id'],
                    'remark' => $data['remark'],
                    'reason' => 'site.join_by_import',
                    'reason_type' => 'import_join',
                );
                $this->becomeStudent($course['id'], $user['id'], $info);
            }

            $member = $this->getCourseMember($course['id'], $user['id']);

            $currentUser = $this->getCurrentUser();
            if (isset($data['isAdminAdded']) && 1 == $data['isAdminAdded']) {
                $message = array(
                    'courseId' => $course['id'],
                    'courseTitle' => $courseSet['title'],
                    'userId' => $currentUser['id'],
                    'userName' => $currentUser['nickname'],
                    'type' => 'create',
                );
                $this->getNotificationService()->notify($member['userId'], 'student-create', $message);
            }

            $infoData = array(
                'courseSetId' => $courseSet['id'],
                'courseId' => $course['id'],
                'title' => CourseTitleUtils::getDisplayedTitle($course),
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'remark' => $data['remark'],
            );

            $this->getLogService()->info(
                'course',
                'add_student',
                "《{$courseSet['title']}》-{$course['title']}(#{$course['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$data['remark']}",
                $infoData
            );
            $this->commit();

            return array($course, $member, $order);
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
            $this->createNewException(UserException::NOTFOUND_USER());
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
            array(
                'reason' => 'course.member.operation.admin_remove_course_student',
                'reason_type' => 'remove',
            )
        );

        $course = $this->getCourseService()->getCourse($courseId);

        $infoData = array(
            'courseId' => $course['id'],
            'title' => CourseTitleUtils::getDisplayedTitle($course),
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
        );

        $this->getLogService()->info(
            'course',
            'remove_student',
            "教学计划《{$course['title']}》(#{$course['id']})，移除学员{$user['nickname']}(#{$user['id']})",
            $infoData
        );

        $this->dispatchEvent('course.quit', $course, array('userId' => $userId, 'member' => $member));

        if ($this->getCurrentUser()->isAdmin()) {
            $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            $this->getNotificationService()->notify(
                $member['userId'],
                'student-remove',
                array(
                    'courseId' => $course['id'],
                    'courseTitle' => $courseSet['title'],
                )
            );
        }

        return $result;
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countMembers($conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getMemberDao()->count($conditions);
    }

    public function findWillOverdueCourses()
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $condition = array(
            'userId' => $currentUser['id'],
            'role' => 'student',
            'deadlineNotified' => 0,
            'deadlineGreaterThan' => time(),
        );
        $courseMembers = $this->getMemberDao()->search($condition, array('createdTime' => 'ASC'), 0, 10);
        $courseIds = ArrayToolkit::column($courseMembers, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $courseMembers = ArrayToolkit::index($courseMembers, 'courseId');

        $shouldNotifyCourses = array();
        $shouldNotifyCourseMembers = array();

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

        return array($shouldNotifyCourses, $shouldNotifyCourseMembers);
    }

    public function getCourseMember($courseId, $userId)
    {
        return $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);
    }

    public function waveMember($id, $diffs)
    {
        return $this->getMemberDao()->wave(array($id), $diffs);
    }

    public function searchMemberIds($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareConditions($conditions);

        if (is_array($sort)) {
            $orderBy = $sort;
        } else {
            $orderBy = array('createdTime' => 'DESC');
        }

        $memberIds = $this->getMemberDao()->searchMemberIds($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::column($memberIds, 'userId');
    }

    public function findMemberUserIdsByCourseId($courseId)
    {
        return ArrayToolkit::column($this->getMemberDao()->findUserIdsByCourseId($courseId), 'userId');
    }

    public function updateMember($id, $fields)
    {
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
        if (!empty($member['levelId'])) {
            // 会员加入的情况下
            $vipNonExpired = $this->isVipMemberNonExpired($course, $member);
        }

        if (0 == $member['deadline']) {
            return $vipNonExpired;
        }

        if ($member['deadline'] > time()) {
            return $vipNonExpired;
        }

        return !$vipNonExpired;
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
            $status = $this->getVipService()->checkUserInMemberLevel($member['userId'], $classroom['vipLevelId']);
        } else {
            $status = $this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']);
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
            array(
                'role' => 'student',
                'courseSetId' => $courseSetId,
                'locked' => 0,
            ),
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $memberIds = array_column($result, 'id');

        $members = $this->getMemberDao()->findByIds($memberIds);
        $members = ArrayToolkit::index($members, 'id');

        $sortedMembers = array();

        foreach ($memberIds as $memberId) {
            $sortedMembers[] = $members[$memberId];
        }

        return $sortedMembers;
    }

    public function getCourseStudentCount($courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'role' => 'student',
        );

        return $this->getMemberDao()->count($conditions);
    }

    public function findCourseTeachers($courseId)
    {
        return $this->getMemberDao()->findByCourseIdAndRole($courseId, 'teacher');
    }

    public function findCourseSetTeachers($courseId)
    {
        return $this->getMemberDao()->findByCourseSetIdAndRole($courseId, 'teacher');
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

        $teacher = array(
            'courseId' => $courseId,
            'courseSetId' => $course['courseSetId'],
            'userId' => $user['id'],
            'role' => 'teacher',
            'isVisible' => 1,
        );

        $teacher = $this->addMember($teacher);

        $fields = array('teacherIds' => array($user['id']));
        $this->getCourseDao()->update($courseId, $fields);
        $this->dispatchEvent('course.teacher.create', new Event($course, array('teacher' => $teacher)));
    }

    public function setCourseTeachers($courseId, $teachers)
    {
        $userIds = ArrayToolkit::column($teachers, 'id');
        $existTeacherMembers = $this->findCourseTeachers($courseId);
        $existTeacherIds = ArrayToolkit::column($existTeacherMembers, 'userId');
        $deleteTeacherIds = array_values(array_diff($existTeacherIds, $userIds));

        $this->dispatchEvent('course.teachers.update.before', new Event($courseId, array(
            'teachers' => $teachers,
            'deleteTeacherIds' => $deleteTeacherIds,
        )));

        $course = $this->getCourseService()->tryManageCourse($courseId);

        if (empty($userIds)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $teacherMembers = $this->buildTeachers($course, $teachers);
        if (empty($teacherMembers)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        // 删除老师
        $this->deleteMemberByCourseIdAndRole($courseId, 'teacher');
        // 删除目前还是学员的成员
        $this->getMemberDao()->batchDelete(array(
            'courseId' => $courseId,
            'userIds' => $userIds,
        ));

        $this->getMemberDao()->batchCreate($teacherMembers);
        $this->updateCourseTeacherIds($courseId, $teachers);
        $addTeachers = array_values(array_diff($userIds, $existTeacherIds));
        $this->dispatchEvent('course.teachers.update', new Event($course, array(
            'teachers' => $teachers,
            'deleteTeachers' => $deleteTeacherIds,
            'addTeachers' => $addTeachers,
        )));
    }

    private function updateCourseTeacherIds($courseId, $teachers)
    {
        $teachers = ArrayToolkit::group($teachers, 'isVisible');

        $visibleTeacherIds = empty($teachers[1]) ? array() : ArrayToolkit::column($teachers[1], 'id');
        $fields = array('teacherIds' => $visibleTeacherIds);
        $course = $this->getCourseDao()->update($courseId, $fields);
    }

    private function buildTeachers($course, $teachers)
    {
        $teacherMembers = array();
        $teachers = ArrayToolkit::index($teachers, 'id');
        $users = $this->getUserService()->findUsersByIds(array_keys($teachers));
        $seq = 0;
        foreach ($teachers as $index => $teacher) {
            $user = $users[$teacher['id']];
            if (in_array('ROLE_TEACHER', $user['roles']) || $course['creator'] == $user['id']) {
                $teacherMembers[] = array(
                    'courseId' => $course['id'],
                    'courseSetId' => $course['courseSetId'],
                    'userId' => $teacher['id'],
                    'role' => 'teacher',
                    'seq' => $seq++,
                    'isVisible' => empty($teacher['isVisible']) ? 0 : 1,
                );
            }
        }

        return $teacherMembers;
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

            $fields = array(
                'teacherIds' => array_diff($course['teacherIds'], array($member['userId'])),
            );
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

        $fields = array('remark' => empty($remark) ? '' : (string) $remark);

        return $this->getMemberDao()->update($member['id'], $fields);
    }

    public function deleteMemberByCourseIdAndRole($courseId, $role)
    {
        return $this->getMemberDao()->deleteByCourseIdAndRole($courseId, $role);
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

        $this->removeMember($member, array(
            'reason' => 'course.member.operation.quit_deadline_reach',
            'reason_type' => 'system',
        ));

        $this->dispatchEvent(
            'course.quit',
            $course,
            array('userId' => $userId, 'member' => $member)
        );

        $this->getCourseDao()->update(
            $courseId,
            array(
                'studentNum' => $this->getCourseStudentCount($courseId),
            )
        );

        $infoData = array(
            'courseId' => $course['id'],
            'title' => CourseTitleUtils::getDisplayedTitle($course),
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
        );

        $this->getLogService()->info(
            'course',
            'remove_student',
            "教学计划《{$course['title']}》(#{$course['id']})，学员({$user['nickname']})因达到有效期退出教学计划(#{$member['id']})",
            $infoData
        );
    }

    public function becomeStudent($courseId, $userId, $info = array())
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        if (!in_array($course['status'], array('published'))) {
            $this->createNewException(CourseException::UNPUBLISHED_COURSE());
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
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
        if ('days' == $course['expiryMode'] && $course['expiryDays'] > 0) {
            $endTime = strtotime(date('Y-m-d', time()).' 23:59:59'); //系统当前时间
            $deadline = $course['expiryDays'] * 24 * 60 * 60 + $endTime;
        } elseif ('date' == $course['expiryMode'] || 'end_date' == $course['expiryMode']) {
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

        $fields = array(
            'courseId' => $courseId,
            'userId' => $userId,
            'courseSetId' => $course['courseSetId'],
            'orderId' => empty($order) ? 0 : $order['id'],
            'deadline' => $deadline,
            'levelId' => empty($info['levelId']) ? 0 : $info['levelId'],
            'role' => 'student',
            'remark' => empty($info['remark']) ? '' : $info['remark'],
            'createdTime' => time(),
            'refundDeadline' => $this->getRefundDeadline(),
        );

        $reason = $this->buildJoinReason($info, $order);
        $member = $this->addMember($fields, $reason);

        $this->refreshMemberNoteNumber($courseId, $userId);

        $this->dispatchEvent(
            'course.join',
            $course,
            array('userId' => $member['userId'], 'member' => $member)
        );

        return $member;
    }

    private function buildJoinReason($info, $order)
    {
        if (ArrayToolkit::requireds($info, array('reason', 'reason_type'))) {
            return ArrayToolkit::parts($info, array('reason', 'reason_type'));
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
            return array();
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        if (!in_array($course['status'], array('published'))) {
            $this->createNewException(CourseException::UNPUBLISHED_COURSE());
        }

        $users = $this->getUserService()->findUsersByIds($memberIds);
        $userIds = ArrayToolkit::column($users, 'id');

        if (empty($users)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $existMembers = $this->searchMembers(array('courseId' => $course['id']), array(), 0, PHP_INT_MAX);
        $existMemberIds = ArrayToolkit::column($existMembers, 'userId');
        $beAddUserIds = array_diff($userIds, $existMemberIds);

        $membersTaskResults = $this->getTaskResult()->searchTaskResults(
            array('courseId' => $course['id'], 'status' => 'finish'),
            array(),
            0,
            PHP_INT_MAX
        );
        $membersTaskResults = ArrayToolkit::group($membersTaskResults, 'userId');

        $courseNotes = $this->getCourseNoteService()->searchNotes(
            array($course['id']),
            array('updatedTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        $membersNotes = ArrayToolkit::group($courseNotes, 'userId');

        $classroomMembers = array();
        if ($classroomId > 0) {
            $classroomMembers = $this->getClassroomService()->findClassroomStudents($classroomId, 0, PHP_INT_MAX);
            $classroomMembers = ArrayToolkit::index($classroomMembers, 'userId');
        }

        $newMembers = array();
        foreach ($beAddUserIds as $userId) {
            $member = array(
                'courseId' => $course['id'],
                'userId' => $userId,
                'courseSetId' => $course['courseSetId'],
                'orderId' => 0,
                'levelId' => 0,
                'role' => 'student',
                'learnedNum' => 0,
                'noteNum' => 0,
                'noteLastUpdateTime' => 0,
                'createdTime' => time(),
                'joinedType' => $classroomId > 0 ? 'classroom' : 'course',
            );

            if ($classroomId > 0 && !empty($classroomMembers[$userId])) {
                $member['classroomId'] = $classroomId;
                $member['deadline'] = $classroomMembers[$userId]['deadline'];
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
        $newMembers = $this->searchMembers(array('courseId' => $course['id']), array(), 0, PHP_INT_MAX);

        $this->getCourseService()->updateCourseStatistics($course['id'], array('studentNum'));
        $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));
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

    public function removeStudent($courseId, $userId, $reason = array())
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ('student' != $member['role'])) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }

        $reason = ArrayToolkit::parts($reason, array('reason', 'reason_type'));

        $this->removeMember($member, $reason);

        $this->getCourseDao()->update(
            $courseId,
            array(
                'studentNum' => $this->getCourseStudentCount($courseId),
            )
        );

        $removeMember = $this->getUserService()->getUser($member['userId']);

        $infoData = array(
            'courseId' => $course['id'],
            'title' => CourseTitleUtils::getDisplayedTitle($course),
            'userId' => $removeMember['id'],
            'nickname' => $removeMember['nickname'],
        );

        $this->getLogService()->info(
            'course',
            'remove_student',
            "教学计划《{$course['title']}》(#{$course['id']})，移除学员({$removeMember['nickname']})(#{$member['id']})",
            $infoData
        );
        $this->dispatchEvent(
            'course.quit',
            $course,
            array('userId' => $member['userId'], 'member' => $member)
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

        $this->getMemberDao()->update($member['id'], array('locked' => 1));
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

        $this->getMemberDao()->update($member['id'], array('locked' => 0));
    }

    public function createMemberByClassroomJoined($courseId, $userId, $classRoomId, array $info = array())
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $deadline = 0;
        if (isset($info['deadline'])) {
            $deadline = $info['deadline'];
        } elseif ('days' == $course['expiryMode']) {
            if (!empty($course['expiryDays'])) {
                $deadline = strtotime('+'.$course['expiryDays'].' days');
            }
        } elseif (!empty($course['expiryEndDate'])) {
            $deadline = $course['expiryEndDate'];
        }

        $learnedNum = $this->getTaskResultService()->countTaskResults(
            array('courseId' => $courseId, 'userId' => $userId, 'status' => 'finish')
        );
        $lastLearnTime = ($learnedNum > 0) ? time() : null;

        $learnedCompulsoryTaskNum = $this->getTaskResultService()->countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $courseId);

        $fields = array(
            'courseId' => $courseId,
            'courseSetId' => $course['courseSetId'],
            'userId' => $userId,
            'orderId' => empty($info['orderId']) ? 0 : $info['orderId'],
            'deadline' => $deadline,
            'levelId' => empty($info['levelId']) ? 0 : $info['levelId'],
            'role' => 'student',
            'remark' => empty($info['orderNote']) ? '' : $info['orderNote'],
            'createdTime' => time(),
            'classroomId' => $classRoomId,
            'joinedType' => 'classroom',
            'learnedNum' => $learnedNum,
            'learnedCompulsoryTaskNum' => $learnedCompulsoryTaskNum,
            'lastLearnTime' => $lastLearnTime,
        );
        $isMember = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);

        if ($isMember) {
            return array();
        }

        $member = $this->addMember(
            $fields,
            array(
                'reason' => 'course.member.operation.reason.join_classroom',
                'reason_type' => 'classroom_join',
            )
        );
        $fields = array(
            'studentNum' => $this->getCourseStudentCount($courseId),
        );
        $this->getCourseDao()->update($courseId, $fields);
        $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));

        $this->dispatchEvent('classroom.course.join', new Event($course, array('member' => $member)));

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

    public function findCoursesByStudentIdAndCourseIds($userId, $courseIds)
    {
        if (empty($courseIds) || 0 == count($courseIds)) {
            return array();
        }

        $courseMembers = $this->getMemberDao()->findByUserIdAndCourseIds($userId, $courseIds);

        return $courseMembers;
    }

    public function becomeStudentByClassroomJoined($courseId, $userId)
    {
        $isCourseStudent = $this->isCourseStudent($courseId, $userId);
        $classroom = $this->getClassroomService()->getClassroomByCourseId($courseId);

        if (!empty($classroom)) {
            $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $userId);

            if (!$isCourseStudent && !empty($member) && array_intersect($member['role'],
                    array('student', 'teacher', 'headTeacher', 'assistant'))
            ) {
                $info = ArrayToolkit::parts($member, array('levelId'));
                $member = $this->createMemberByClassroomJoined($courseId, $userId, $member['classroomId'], $info);

                return $member;
            }
        }

        return array();
    }

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['date'])) {
            $dates = array(
                'yesterday' => array(
                    strtotime('yesterday'),
                    strtotime('today'),
                ),
                'today' => array(
                    strtotime('today'),
                    strtotime('tomorrow'),
                ),
                'this_week' => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week'),
                ),
                'last_week' => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week'),
                ),
                'next_week' => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week')),
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'),
                    strtotime('first day of next month midnight'),
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight'),
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
                ),
            );

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
            array(
                'noteNum' => (int) $number,
                'noteLastUpdateTime' => time(),
            )
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
                    array(
                        'deadline' => $deadline,
                    )
                );
            }
        }
    }

    public function checkDayAndWaveTypeForUpdateDeadline($courseId, $userIds, $day, $waveType = 'plus')
    {
        $members = $this->searchMembers(
            array('userIds' => $userIds, 'courseId' => $courseId),
            array('deadline' => 'ASC'),
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
        $date = TimeMachine::isTimestamp($date) ? $date : strtotime($date.' 23:59:59');
        if ($this->checkDeadlineForUpdateDeadline($courseId, $userIds, $date)) {
            foreach ($userIds as $userId) {
                $member = $this->getMemberDao()->getByCourseIdAndUserId($courseId, $userId);
                $this->getMemberDao()->update(
                    $member['id'],
                    array(
                        'deadline' => $date,
                    )
                );
            }
        }
    }

    public function checkDeadlineForUpdateDeadline($courseId, $userIds, $date)
    {
        $members = $this->searchMembers(
            array('userIds' => $userIds, 'courseId' => $courseId),
            array('deadline' => 'ASC'),
            0,
            PHP_INT_MAX
        );
        $member = array_shift($members);
        if ($date < $member['deadline'] || time() > $date) {
            return false;
        }

        return true;
    }

    public function updateMemberDeadlineByClassroomIdAndUserId($classroomId, $userId, $deadline)
    {
        if (empty($classroomId) || empty($userId)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getMemberDao()->updateByClassroomIdAndUserId($classroomId, $userId, array(
            'deadline' => $deadline,
        ));
    }

    public function updateMembersDeadlineByClassroomId($classroomId, $deadline)
    {
        return $this->getMemberDao()->updateByClassroomId($classroomId, array('deadline' => $deadline));
    }

    public function findMembersByCourseIdAndRole($courseId, $role)
    {
        return $this->getMemberDao()->findByCourseIdAndRole($courseId, $role);
    }

    public function findDailyIncreaseNumByCourseIdAndRoleAndTimeRange($courseId, $role, $timeRange = array(), $format = '%Y-%m-%d')
    {
        $conditions = array(
            'courseId' => $courseId,
            'role' => $role,
        );
        if (!empty($timeRange)) {
            $conditions['startTimeGreaterThan'] = strtotime($timeRange['startDate']);
            $conditions['startTimeLessThan'] = empty($timeRange['endDate']) ? time() : strtotime($timeRange['endDate'].'+1 day');
        }

        return $this->getMemberDao()->searchMemberCountsByConditionsGroupByCreatedTimeWithFormat($conditions, $format);
    }

    public function findMembersByIds($ids)
    {
        return $this->getMemberDao()->findByIds($ids);
    }

    public function countStudentMemberByCourseSetId($courseSetId)
    {
        $conditions = array(
            'courseSetId' => $courseSetId,
            'role' => 'student',
        );

        return $this->getMemberDao()->count($conditions);
    }

    protected function createOrder($courseId, $userId, $data)
    {
        $courseProduct = $this->getOrderFacadeService()->getOrderProduct('course', array('targetId' => $courseId));

        $params = array(
            'created_reason' => $data['remark'],
            'source' => $data['source'],
            'create_extra' => $data,
            'deducts' => empty($data['deducts']) ? array() : $data['deducts'],
        );

        return $this->getOrderFacadeService()->createSpecialOrder($courseProduct, $userId, $params);
    }

    protected function createOperateRecord($member, $operateType, $reason)
    {
        $currentUser = $this->getCurrentUser();
        $data['member'] = $member;
        $course = $this->getCourseService()->getCourse($member['courseId']);
        $record = array(
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
        );
        $otherMemberCount = $this->countMembers(array(
            'excludeIds' => array($member['id']),
            'courseSetId' => $member['courseSetId'],
            'userId' => $member['userId'],
            'role' => 'student',
        ));

        if (empty($otherMemberCount)) {
            'join' == $operateType ? $record['join_course_set'] = 1 : $record['exit_course_set'] = 1;
        }

        $record = array_merge($record, $reason);
        $record = $this->getMemberOperationService()->createRecord($record);

        return $record;
    }

    private function addMember($member, $reason = array())
    {
        try {
            $this->beginTransaction();
            $member = $this->getMemberDao()->create($member);
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

    private function removeMember($member, $reason = array())
    {
        try {
            $this->beginTransaction();
            $result = $this->getMemberDao()->delete($member['id']);
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
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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
     * @return TaskService
     */
    protected function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }
}
