<?php


namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Order\Service\OrderService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\User\Service\UserService;
use Symfony\Component\EventDispatcher\Event;
use Topxia\Common\ArrayToolkit;

/**
 * Class MemberServiceImpl
 * @package Biz\Course\Service\Impl
 * 所有api 均迁移自 courseService 中的对member操作的api
 */
class MemberServiceImpl extends BaseService implements MemberService
{

    public function becomeStudentAndCreateOrder($userId, $courseId, $data)
    {
        if (!ArrayToolkit::requireds($data, array("price", "remark"))) {
            throw $this->createServiceException('parameter is invalid!');
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("user #{$userId} does not exist");
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("course #{$courseId} does not exist ");
        }

        if ($this->getCourseService()->isCourseStudent($course['id'], $user['id'])) {
            throw $this->createNotFoundException('用户已经是学员，不能添加！');
        }

        $orderTitle = "购买课程《{$course['title']}》";

        if (isset($data["isAdminAdded"]) && $data["isAdminAdded"] == 1) {
            $orderTitle = $orderTitle.'(管理员添加)';
            $payment    = 'outside';
        } else {
            $payment = 'none';
        }

        if (empty($data['price'])) {
            $data['price'] = 0;
        }

        $order = $this->getOrderService()->createOrder(array(
            'userId'     => $user['id'],
            'title'      => $orderTitle,
            'targetType' => 'course',
            'targetId'   => $course['id'],
            'amount'     => $data['price'],
            'totalPrice' => $course['price'],
            'payment'    => $payment,
            'snPrefix'   => 'C'
        ));

        $this->getOrderService()->payOrder(array(
            'sn'       => $order['sn'],
            'status'   => 'success',
            'amount'   => $order['amount'],
            'paidTime' => time()
        ));

        $info = array(
            'orderId'         => $order['id'],
            'note'            => $data['remark'],
            'becomeUseMember' => isset($data['becomeUseMember']) ? $data['becomeUseMember'] : false
        );

        $this->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info);

        $member = $this->getCourseMember($course['id'], $user['id']);

        if (isset($data["isAdminAdded"]) && $data["isAdminAdded"] == 1) {
            $this->getNotificationService()->notify($member['userId'], 'student-create', array(
                'courseId'    => $course['id'],
                'courseTitle' => $course['title']
            ));
        }

        $this->getLogService()->info('course', 'add_student', "课程《{$course['title']}》(#{$course['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$data['remark']}");

        return array($course, $member, $order);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);
        return $this->getMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchMember($conditions, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);
       // return $this->getMemberDao()->search($conditions, $orderBy = array(), $start, $limit);
    }

    public function searchMemberCount($conditions)
    {
        $conditions = $this->_prepareConditions($conditions);
        return $this->getMemberDao()->count($conditions);
    }

    public function searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit)
    {
        //return $this->getMemberDao()->searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit);
    }

    public function findWillOverdueCourses()
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            throw $this->createServiceException('用户未登录');
        }

        $courseMembers = $this->getMemberDao()->findCourseMembersByUserId($currentUser["id"]);

        $courseIds = ArrayToolkit::column($courseMembers, "courseId");
        $courses   = $this->getCourseService()->findCoursesByIds($courseIds);

        $courseMembers = ArrayToolkit::index($courseMembers, "courseId");

        $shouldNotifyCourses       = array();
        $shouldNotifyCourseMembers = array();

        $currentTime = time();

        foreach ($courses as $key => $course) {
            $courseMember = $courseMembers[$course["id"]];

            if ($course["expiryDay"] > 0 && $currentTime < $courseMember["deadline"] && (10 * 24 * 60 * 60 + $currentTime) > $courseMember["deadline"]) {
                $shouldNotifyCourses[]       = $course;
                $shouldNotifyCourseMembers[] = $courseMember;
            }
        }

        return array($shouldNotifyCourses, $shouldNotifyCourseMembers);
    }

    public function getCourseMember($courseId, $userId)
    {
        return $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);
    }

    public function searchMemberIds($conditions, $sort, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);

        if (is_array($sort)) {
            $orderBy = $sort;
        } else {
            $orderBy = array('createdTime', 'DESC');
        }

        return $this->getMemberDao()->searchMemberIds($conditions, $orderBy, $start, $limit);
    }


    public function findMemberUserIdsByCourseId($courseId)
    {
        return $this->getMemberDao()->findMemberUserIdsByCourseId($courseId);
    }

    public function updateCourseMember($id, $fields)
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
            throw $this->createServiceException('course, member参数不能为空');
        }

        if ($member['deadline'] == 0) {
            return true;
        }

        if ($member['deadline'] > time()) {
            return true;
        }

        return false;
    }

    public function findCourseStudents($courseId, $start, $limit)
    {
        return $this->getMemberDao()->findMembersByCourseIdAndRole($courseId, 'student', $start, $limit);
    }

    public function findCourseStudentsByCourseIds($courseIds)
    {
        return $this->getMemberDao()->findMembersByCourseIds($courseIds);
    }

    public function getCourseStudentCount($courseId)
    {
        return $this->getMemberDao()->findMemberCountByCourseIdAndRole($courseId, 'student');
    }

    protected function findCourseTeachers($courseId)
    {
        return $this->getMemberDao()->findMembersByCourseIdAndRole($courseId, 'teacher', 0, self::MAX_TEACHER);
    }

    public function isCourseTeacher($courseId, $userId)
    {
        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (!$member) {
            return false;
        } else {
            return empty($member) || $member['role'] != 'teacher' ? false : true;
        }
    }

    public function isCourseStudent($courseId, $userId)
    {
        $member = $this->isCourseMember($courseId, $userId);

        if (!$member) {
            return false;
        } else {
            return empty($member) || $member['role'] != 'student' ? false : true;
        }
    }

    public function isCourseMember($courseId, $userId)
    {
        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        return empty($member) ? false : true;
    }

    public function setCourseTeachers($courseId, $teachers)
    {
        // 过滤数据
        $teacherMembers = array();

        foreach (array_values($teachers) as $index => $teacher) {
            if (empty($teacher['id'])) {
                throw $this->createServiceException("教师ID不能为空，设置课程(#{$courseId})教师失败");
            }

            $user = $this->getUserService()->getUser($teacher['id']);

            if (empty($user)) {
                throw $this->createServiceException("用户不存在或没有教师角色，设置课程(#{$courseId})教师失败");
            }

            $teacherMembers[] = array(
                'courseId'    => $courseId,
                'userId'      => $user['id'],
                'role'        => 'teacher',
                'seq'         => $index,
                'isVisible'   => empty($teacher['isVisible']) ? 0 : 1,
                'createdTime' => time()
            );
        }

        // 先清除所有的已存在的教师学员
        $existTeacherMembers = $this->findCourseTeachers($courseId);

        foreach ($existTeacherMembers as $member) {
            $this->getMemberDao()->delete($member['id']);
        }

        // 逐个插入新的教师的学员数据
        $visibleTeacherIds = array();

        foreach ($teacherMembers as $member) {
            // 存在学员信息，说明该用户先前是学生学员，则删除该学员信息。
            $existMember = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $member['userId']);

            if ($existMember) {
                $this->getMemberDao()->delete($existMember['id']);
            }

            $member = $this->getMemberDao()->create($member);

            if ($member['isVisible']) {
                $visibleTeacherIds[] = $member['userId'];
            }
        }

        $this->getLogService()->info('course', 'update_teacher', "更新课程#{$courseId}的教师", $teacherMembers);

        // 更新课程的teacherIds，该字段为课程可见教师的ID列表
        $fields = array('teacherIds' => $visibleTeacherIds);
        $course = $this->getCourseDao()->update($courseId, $fields);

        $this->dispatchEvent("course.teacher.update", array(
            "courseId" => $courseId,
            "course"   => $course,
            'teachers' => $teachers
        ));
    }

    /**
     * @todo 当用户拥有大量的课程老师角色时，这个方法效率是有就有问题咯！鉴于短期内用户不会拥有大量的课程老师角色，先这么做着。
     */
    public function cancelTeacherInAllCourses($userId)
    {
        $count   = $this->getMemberDao()->findMemberCountByUserIdAndRole($userId, 'teacher', false);
        $members = $this->getMemberDao()->findMembersByUserIdAndRole($userId, 'teacher', 0, $count, false);

        foreach ($members as $member) {
            $course = $this->getCourseService()->getCourse($member['courseId']);

            $this->getMemberDao()->delete($member['id']);

            $fields = array(
                'teacherIds' => array_diff($course['teacherIds'], array($member['userId']))
            );
            $this->getCourseDao()->update($member['courseId'], $fields);
        }

        $this->getLogService()->info('course', 'cancel_teachers_all', "取消用户#{$userId}所有的课程老师角色");
    }

    public function remarkStudent($courseId, $userId, $remark)
    {
        $member = $this->getCourseMember($courseId, $userId);

        if (empty($member)) {
            throw $this->createServiceException('课程学员不存在，备注失败!');
        }

        $fields = array('remark' => empty($remark) ? '' : (string)$remark);
        return $this->getMemberDao()->update($member['id'], $fields);
    }

    public function deleteMemberByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getMemberDao()->deleteMemberByCourseIdAndUserId($courseId, $userId);
    }

    public function deleteMemberByCourseIdAndRole($courseId, $role)
    {
        return $this->getMemberDao()->deleteMemberByCourseIdAndRole($courseId, $role);
    }

    public function deleteMemberByCourseId($courseId)
    {
        return $this->getMemberDao()->deleteMembersByCourseId($courseId);
    }

    public function findMembersByUserIdAndJoinType($userId, $joinedType = 'course')
    {
        $courseIds = $this->getMemberDao()->findMembersByUserIdAndJoinType($userId, $joinedType);
        return ArrayToolkit::column($courseIds, 'courseId');
    }

    public function quitCourseByDeadlineReach($userId, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("课程(#{$courseId})不存在，退出课程失败。");
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ($member['role'] != 'student')) {
            throw $this->createServiceException("用户(#{$userId})不是课程(#{$courseId})的学员，退出课程失败。");
        }

        $isNonExpired = $this->isMemberNonExpired($course, $member);

        if ($isNonExpired) {
            throw $this->createServiceException("用户(#{$userId})还未达到有效期，不能退出课程。");
        }

        //查询出订单
        $order = $this->getOrderService()->getOrder($member['orderId']);
        $user  = $this->getUserService()->getUser($userId);
        if (!empty($order)) {
            $reason = array(
                'type'     => 'other',
                'note'     => '达到有效期，用户自己退出',
                'operator' => $user['id']
            );
            $this->getOrderService()->applyRefundOrder($order['id'], null, $reason);
        }

        $this->getMemberDao()->delete($member['id']);
        $this->dispatchEvent(
            'learning.quit',
            new Event($course, array('userId' => $userId))
        );

        $this->getCourseDao()->update($courseId, array(
            'studentNum' => $this->getCourseStudentCount($courseId)
        ));

        $this->getLogService()->info('course', 'remove_student', "课程《{$course['title']}》(#{$course['id']})，学员({$user['nickname']})因达到有效期退出课程(#{$member['id']})");
    }

    public function becomeStudent($courseId, $userId, $info = array())
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($course['status'], array('published'))) {
            throw $this->createServiceException('不能加入未发布课程');
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户(#{$userId})不存在，加入课程失败！");
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if ($member) {
            throw $this->createServiceException("用户(#{$userId})已加入该课程！");
        }

        $levelChecked = '';

        if (!empty($info['becomeUseMember'])) {
            $levelChecked = $this->getVipService()->checkUserInMemberLevel($user['id'], $course['vipLevelId']);

            if ($levelChecked != 'ok') {
                throw $this->createServiceException($this->getKernel()->trans('用户(#%userId%)不能以会员身份加入课程！', array('%userId%' => $userId)));
            }

            $userMember = $this->getVipService()->getMemberByUserId($user['id']);
        }

        //按照课程有效期模式计算学员有效期
        $deadline = 0;
        if ($course['expiryDay'] > 0) {
            if ($course['expiryMode'] == 'days') {
                $deadline = $course['expiryDay'] * 24 * 60 * 60 + time();
            }
            if ($course['expiryMode'] == 'date') {
                $deadline = $course['expiryDay'];
            }
        }

        if (!empty($info['orderId'])) {
            $order = $this->getOrderService()->getOrder($info['orderId']);

            if (empty($order)) {
                throw $this->createServiceException("订单(#{$info['orderId']}})不存在，加入课程失败！");
            }
        } else {
            $order = null;
        }

        $conditions = array(
            'userId'   => $userId,
            'status'   => 'finished',
            'courseId' => $courseId
        );
        //TODO course2.0 获取学习了的task数量
        $count      = 0; //$this->getLessonLearnDao()->searchLearnCount($conditions);
        $fields     = array(
            'courseId'    => $courseId,
            'userId'      => $userId,
            'orderId'     => empty($order) ? 0 : $order['id'],
            'deadline'    => $deadline,
            'levelId'     => empty($info['becomeUseMember']) ? 0 : $userMember['levelId'],
            'role'        => 'student',
            'remark'      => empty($order['note']) ? '' : $order['note'],
            'learnedNum'  => $count,
            'createdTime' => time()
        );

        if (empty($fields['remark'])) {
            $fields['remark'] = empty($info['note']) ? '' : $info['note'];
        }

        $member = $this->getMemberDao()->create($fields);

        $this->setMemberNoteNumber(
            $courseId,
            $userId,
            $this->getNoteDao()->getNoteCountByUserIdAndCourseId($userId, $courseId)
        );

        $setting = $this->getSettingService()->get('course', array());

        if (!empty($setting['welcome_message_enabled']) && !empty($course['teacherIds'])) {
            $message = $this->getWelcomeMessageBody($user, $course);
            $this->getMessageService()->sendMessage($course['teacherIds'][0], $user['id'], $message);
        }

        $fields = array(
            'studentNum' => $this->getCourseStudentCount($courseId)
        );

        if ($order) {
            $fields['income'] = $this->getOrderService()->sumOrderPriceByTarget('course', $courseId);
        }

        $this->getCourseDao()->updateCourse($courseId, $fields);
        $this->dispatchEvent(
            'course.join',
            new Event($course, array('userId' => $member['userId'], 'member' => $member))
        );
        return $member;
    }


    public function removeStudent($courseId, $userId)
    {
        $course = $this->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException($this->getKernel()->trans('课程(#%courseId%)不存在，退出课程失败。', array('%courseId%' => $courseId)));
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ($member['role'] != 'student')) {
            throw $this->createServiceException($this->getKernel()->trans('用户(#%userId%)不是课程(#%courseId%)的学员，退出课程失败。', array('%userId%' => $userId, '%courseId%' => $courseId)));
        }

        $this->getMemberDao()->delete($member['id']);

        $this->getCourseDao()->update($courseId, array(
            'studentNum' => $this->getCourseStudentCount($courseId)
        ));

        $removeMember = $this->getUserService()->getUser($member['userId']);

        $this->getLogService()->info('course', 'remove_student', "课程《{$course['title']}》(#{$course['id']})，移除学员({$removeMember['nickname']})(#{$member['id']})");
        $this->dispatchEvent(
            'course.quit',
            new Event($course, array('userId' => $member['userId'], 'member' => $member))
        );
    }

    public function lockStudent($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException($this->getKernel()->trans('课程(#%courseId%)不存在，封锁学员失败。', array('%courseId%' => $courseId)));
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ($member['role'] != 'student')) {
            throw $this->createServiceException($this->getKernel()->trans('用户(#%userId%)不是课程(#%courseId%)的学员，封锁学员失败。', array('%userId%' => $userId, '%courseId%' => $courseId)));
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
            throw $this->createNotFoundException($this->getKernel()->trans('课程(#%courseId%)不存在，封锁学员失败。', array('%courseId%' => $courseId)));
        }

        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if (empty($member) || ($member['role'] != 'student')) {
            throw $this->createServiceException($this->getKernel()->trans('用户(#%userId%)不是课程(#%courseId%)的学员，解封学员失败。', array('%userId%' => $userId, '%courseId%' => $courseId)));
        }

        if (empty($member['locked'])) {
            return;
        }

        $this->getMemberDao()->update($member['id'], array('locked' => 0));
    }

    public function createMemberByClassroomJoined($courseId, $userId, $classRoomId, array $info = array())
    {
        $fields   = array(
            'courseId'    => $courseId,
            'userId'      => $userId,
            'orderId'     => empty($info["orderId"]) ? 0 : $info["orderId"],
            'deadline'    => empty($info['deadline']) ? 0 : $info['deadline'],
            'levelId'     => empty($info['levelId']) ? 0 : $info['levelId'],
            'role'        => 'student',
            'remark'      => empty($info["orderNote"]) ? '' : $info["orderNote"],
            'createdTime' => time(),
            'classroomId' => $classRoomId,
            'joinedType'  => 'classroom'
        );
        $isMember = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $userId);

        if ($isMember) {
            return array();
        }

        $member = $this->getMemberDao()->create($fields);
        $fields = array(
            'studentNum' => $this->getCourseStudentCount($courseId)
        );
        $this->getCourseDao()->update($courseId, $fields);
        return $member;
    }

    public function findCoursesByStudentIdAndCourseIds($studentId, $courseIds)
    {
        if (empty($courseIds) || count($courseIds) == 0) {
            return array();
        }

        $courseMembers = $this->getMemberDao()->findCoursesByStudentIdAndCourseIds($studentId, $courseIds);
        return $courseMembers;
    }

    public function becomeStudentByClassroomJoined($courseId, $userId)
    {
        $isCourseStudent = $this->isCourseStudent($courseId, $userId);
        $classroom       = $this->getClassroomService()->getClassroomByCourseId($courseId);

        if ($classroom['classroomId']) {
            $member = $this->getClassroomService()->getClassroomMember($classroom['classroomId'], $userId);

            if (!$isCourseStudent && !empty($member) && array_intersect($member['role'], array('student', 'teacher', 'headTeacher', 'assistant'))) {
                $info   = ArrayToolkit::parts($member, array('levelId'));
                $member = $this->createMemberByClassroomJoined($courseId, $userId, $member["classroomId"], $info);
                return $member;
            }
        }

        return array();
    }


    protected function _prepareConditions($conditions)
    {
        if (isset($conditions['date'])) {
            $dates = array(
                'yesterday'  => array(
                    strtotime('yesterday'),
                    strtotime('today')
                ),
                'today'      => array(
                    strtotime('today'),
                    strtotime('tomorrow')
                ),
                'this_week'  => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week')
                ),
                'last_week'  => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week')
                ),
                'next_week'  => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week'))
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'),
                    strtotime('first day of next month midnight')
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight')
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight'))
                )
            );

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
                $conditions['startTimeLessThan']    = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }

        if (isset($conditions['creator']) && !empty($conditions['creator'])) {
            $user                 = $this->getUserService()->getUserByNickname($conditions['creator']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['creator']);
        }

        if (isset($conditions['nickname'])) {
            $user                 = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    public function setMemberNoteNumber($courseId, $userId, $number)
    {
        $member = $this->getCourseMember($courseId, $userId);

        if (empty($member)) {
            return false;
        }

        $this->getMemberDao()->update($member['id'], array(
            'noteNum'            => (int) $number,
            'noteLastUpdateTime' => time()
        ));

        return true;
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

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    protected function getMessageService()
    {
        return $this->createService('User:MessageService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getNoteDao()
    {
        return $this->createDao('Course:CourseNoteDao');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }


    protected function getVipService()
    {
        return $this->createService('Vip:Vip.VipService');
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
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->biz->service('Taxonomy:CategoryService');
    }
}