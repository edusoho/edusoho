<?php

namespace Biz\ItemBankExercise\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExerciseBindEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'exercise.bind' => 'onExerciseBind',
            'exercise.unBind' => 'onExerciseUnBind',
            'exercise.bind.add.student' => 'onExerciseBindAddStudent',
            'exercise.bind.remove.student' => 'onExerciseBindRemoveStudent',
            'exercise.banLearn' => 'onExerciseBanLearn',
            'exercise.canLearn' => 'onExerciseCanLearn',
            'exercise.member.deadline.update' => 'onExerciseMemberDeadlineUpdate',
        ];
    }

    /** 课程/班级绑定题库
     * @return void
     */
    public function onExerciseBind(Event $event)
    {
        $params = $event->getSubject(); // exerciseBinds, bindType, bindId
        $userIds = $this->getStudentIds($params['bindType'], $params['bindId']);
        if (empty($userIds)) {
            return;
        }
        foreach ($params['exerciseBinds'] as $exerciseBind) {
            // 查询学员是不是当前题库练习的成员
            $exerciseUsers = $this->getExerciseMemberService()->search(['userIds' => $userIds, 'exerciseId' => $exerciseBind['itemBankExerciseId']], [], 0, PHP_INT_MAX);
            // 拆分是题库成员的部分，不是题库成员的部分
            $exerciseMemberUserIds = array_column($exerciseUsers, 'userId');
            $notMemberUserIds = array_diff($userIds, $exerciseMemberUserIds);
            $exercise = $this->getExerciseService()->get($exerciseBind['itemBankExerciseId']);
            $exercise = $this->resetExerciseDeadLine($params['bindType'], $params['bindId'], $exercise);
            if (!empty($notMemberUserIds)) {
                $this->getExerciseMemberService()->batchBecomeStudent([$exerciseBind['itemBankExerciseId']], $notMemberUserIds, ['joinedChannel' => 'bind_join'], $exercise);
            }
            if (!empty($exerciseUsers)) {
                $exerciseAutoJoinRecords = $this->buildExerciseAutoJoinRecords(array_column($exerciseUsers, 'userId'), $exerciseBind);
                $this->updateMemberExpiredTime($exerciseAutoJoinRecords);
            }
            $exerciseAutoJoinRecords = $this->buildExerciseAutoJoinRecords($userIds, $exerciseBind);
            $existingRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId($userIds, $exerciseBind['itemBankExerciseId']);
            $exerciseAutoJoinRecords = $this->filterExistAutoJoinRecords($exerciseAutoJoinRecords, $existingRecords);
            $this->getItemBankExerciseService()->batchCreateExerciseAutoJoinRecord($exerciseAutoJoinRecords);
        }
    }

    /** 课程/班级解除绑定题库
     * @return void
     */
    public function onExerciseUnBind(Event $event)
    {
        $params = $event->getSubject();
        $exerciseBind = $this->getExerciseService()->getExerciseBindById($params['id']);
        if (empty($exerciseBind)) {
            $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
            foreach ($exerciseBinds as $exerciseBind) {
                $this->unBindByExerciseBind($exerciseBind);
            }
        } else {
            $this->unBindByExerciseBind($exerciseBind);
        }
    }

    protected function unBindByExerciseBind($exerciseBind)
    {
        $userIds = $this->getStudentIds($exerciseBind['bindType'], $exerciseBind['bindId']);
        if (empty($userIds)) {
            return;
        }
        // 查询成员、获取成员IDs
        $autoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId($userIds, $exerciseBind['itemBankExerciseId']);
        list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($autoJoinRecords, $exerciseBind['id']);
        // 只有一条记录直接移除学员
        if (!empty($singleExerciseAutoJoinRecords)) {
            $this->getExerciseMemberService()->batchRemoveStudent($exerciseBind['itemBankExerciseId'], array_column($singleExerciseAutoJoinRecords, 'userId'));
        }
        $multipleExerciseAutoJoinRecords = array_filter($multipleExerciseAutoJoinRecords, function ($record) use ($exerciseBind) {
            return $record['itemBankExerciseBindId'] != $exerciseBind['id'];
        });
        // 有多条记录重新计算有效期
        $this->updateMemberExpiredTime($multipleExerciseAutoJoinRecords);
        // 移除自动加入记录
        $this->getExerciseService()->deleteExerciseAutoJoinRecordByExerciseBindId($exerciseBind['id']);
    }

    /** 课程/班级添加学员
     * @return void
     */
    public function onExerciseBindAddStudent(Event $event)
    {   // 当只有一个加入记录时，移除学员，移除自动加入移除
        // 当有多个加入记录时，移除自动加入记录，更新有效期
        // 需要查询所有的绑定题库
        //
        $params = $event->getSubject(); // bindType、bindId、userIds
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        if (empty($exerciseBinds)) {
            return;
        }
        foreach ($exerciseBinds as $exerciseBind) {
            // 查询学员是不是当前题库练习的成员
            $exerciseUsers = $this->getExerciseMemberService()->search(['userIds' => $params['userIds'], 'exerciseId' => $exerciseBind['itemBankExerciseId']], [], 0, PHP_INT_MAX);
            // 拆分是题库成员的部分，不是题库成员的部分
            $exerciseMemberUserIds = array_column($exerciseUsers, 'userId');
            $notMemberUserIds = array_diff($params['userIds'], $exerciseMemberUserIds);
            $exercise = $this->getExerciseService()->get($exerciseBind['itemBankExerciseId']);
            $exercise = $this->resetExerciseDeadLine($params['bindType'], $params['bindId'], $exercise);
            if (!empty($notMemberUserIds)) {
                $this->getExerciseMemberService()->batchBecomeStudent([$exerciseBind['itemBankExerciseId']], $notMemberUserIds, ['joinedChannel' => 'bind_join'], $exercise);
            }
            if (!empty($exerciseUsers)) {
                $exerciseAutoJoinRecords = $this->buildExerciseAutoJoinRecords(array_column($exerciseUsers, 'userId'), $exerciseBind);
                $savedAutoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId(array_column($exerciseUsers, 'userId'), $exerciseBind['itemBankExerciseId']);
                $this->updateMemberExpiredTime(array_merge($exerciseAutoJoinRecords, $savedAutoJoinRecords));
            }
            $exerciseAutoJoinRecords = $this->buildExerciseAutoJoinRecords($params['userIds'], $exerciseBind);
            $existingRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId($params['userIds'], $exerciseBind['itemBankExerciseId']);
            $exerciseAutoJoinRecords = $this->filterExistAutoJoinRecords($exerciseAutoJoinRecords, $existingRecords);
            $this->getItemBankExerciseService()->batchCreateExerciseAutoJoinRecord($exerciseAutoJoinRecords);
        }
    }

    /** 课程/班级移除学员
     * @return void
     */
    public function onExerciseBindRemoveStudent(Event $event)
    {
        $params = $event->getSubject(); // bindType、bindId、userIds
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        foreach ($exerciseBinds as $exerciseBind) {
            // 查询成员、获取成员IDs
            $autoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId($params['userIds'], $exerciseBind['itemBankExerciseId']);
            list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($autoJoinRecords, $exerciseBind['id']);
            // 只有一条记录直接移除学员
            if (!empty($singleExerciseAutoJoinRecords)) {
                $this->getExerciseMemberService()->batchRemoveStudent($exerciseBind['itemBankExerciseId'], array_column($singleExerciseAutoJoinRecords, 'userId'));
            }
            // 有多条记录重新计算有效期
            $this->updateMemberExpiredTime($multipleExerciseAutoJoinRecords);
            // 移除自动加入记录
            $this->getExerciseService()->deleteExerciseAutoJoinRecordByUserIdsAndExerciseBindId($params['userIds'], $exerciseBind['id']);
        }
    }

    /** 关闭班级/关闭课程时，禁止学习
     * @return void
     */
    public function onExerciseBanLearn(Event $event)
    {
        $params = $event->getSubject();
        if ('courseSet' == $params['bindType']) {
            $courses = $this->getCourseService()->findCoursesByCourseSetId($params['bindId']);
            foreach ($courses as $course) {
                $this->exerciseBanLearnByCourse(['bindType' => 'course', 'bindId' => $course['id']]);
            }
        } else {
            $this->exerciseBanLearnByCourse($params);
        }
    }

    protected function exerciseBanLearnByCourse($params)
    {
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        $userIds = $this->getStudentIds($params['bindType'], $params['bindId']);
        foreach ($exerciseBinds as $exerciseBind) {
            $autoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId($userIds, $exerciseBind['itemBankExerciseId']);
            list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($autoJoinRecords, $exerciseBind['id']);
            if (!empty($singleExerciseAutoJoinRecords)) {
                $this->batchBanLearn($singleExerciseAutoJoinRecords);
            }
            $waitUpdateAutoJoin = array_filter($autoJoinRecords, function ($autoJoinRecord) use ($exerciseBind) {
                return $autoJoinRecord['itemBankExerciseBindId'] == $exerciseBind['id'];
            });
            foreach ($waitUpdateAutoJoin as &$autoJoinRecord) {
                $autoJoinRecord['isValid'] = 0;
            }
            if (!empty($waitUpdateAutoJoin)) {
                $this->getExerciseService()->batchUpdateExerciseAutoJoinRecord($waitUpdateAutoJoin);
            }
            $multipleExerciseAutoJoinRecords = array_filter($multipleExerciseAutoJoinRecords, function ($autoJoinRecord) use ($exerciseBind) {
                return $autoJoinRecord['itemBankExerciseBindId'] != $exerciseBind['id'];
            });
            $this->updateMemberExpiredTime($multipleExerciseAutoJoinRecords);
        }
    }

    /** 发布班级/发布课程时，开启学习
     * @return void
     */
    public function onExerciseCanLearn(Event $event)
    {
        $params = $event->getSubject();
        if ('courseSet' == $params['bindType']) {
            $courses = $this->getCourseService()->findCoursesByCourseSetId($params['bindId']);
            foreach ($courses as $course) {
                $this->exerciseCanLearnByCourse(['bindType' => 'course', 'bindId' => $course['id']]);
            }
        } else {
            $this->exerciseCanLearnByCourse($params);
        }
    }

    protected function exerciseCanLearnByCourse($params)
    {
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        $userIds = $this->getStudentIds($params['bindType'], $params['bindId']);
        if (empty($exerciseBinds) || empty($userIds)) {
            return;
        }
        foreach ($exerciseBinds as $exerciseBind) {
            $autoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId($userIds, $exerciseBind['itemBankExerciseId']);
            $invalidAutoJoin = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseIdAndBindId($userIds, $exerciseBind['itemBankExerciseId'], $exerciseBind['id']);
            $autoJoinRecords = array_merge($autoJoinRecords, $invalidAutoJoin);
            list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($autoJoinRecords, $exerciseBind['id']);
            if (!empty($singleExerciseAutoJoinRecords)) {
                $this->batchCanLearn($singleExerciseAutoJoinRecords);
            }
            foreach ($invalidAutoJoin as &$autoJoinRecord) {
                $autoJoinRecord['isValid'] = 1;
            }
            if (!empty($invalidAutoJoin)) {
                $this->getExerciseService()->batchUpdateExerciseAutoJoinRecord($invalidAutoJoin);
            }

            $this->updateMemberExpiredTime($multipleExerciseAutoJoinRecords);
        }
    }

    /**
     * 时间
     *
     * @return void
     */
    public function onExerciseMemberDeadlineUpdate(Event $event)
    {
        $params = $event->getSubject();
        if (empty($params['userIds']) && empty($params['all'])) {
            return;
        }
        $bindTypeMembers = [];
        if ($params['all']) {
            if ('course' == $params['bindType']) {
                $bindTypeMembers = $this->getCourseMemberService()->searchMembers(['courseId' => $params['bindId'], 'role' => 'student'], ['id' => 'ASC'], 0, PHP_INT_MAX, ['userId', 'deadline']);
            } else {
                $bindTypeMembers = $this->getClassroomService()->searchMembers(['classroomId' => $params['bindId'], 'role' => 'student'], ['id' => 'ASC'], 0, PHP_INT_MAX, ['userId', 'deadline']);
            }
        }
        $userIds = $bindTypeMembers ? array_column($bindTypeMembers, 'userId') : $params['userIds'];
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        foreach ($exerciseBinds as $exerciseBind) {
            $exerciseAutoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId($userIds, $exerciseBind['itemBankExerciseId']);
            $this->updateMemberExpiredTime($exerciseAutoJoinRecords);
        }
    }

    /** 根据加入次数分类用户加入记录
     * @param $exerciseBinds
     *
     * @return array
     */
    protected function categorizeUserRecordsByCount($exerciseAutoJoinRecords, $exerciseBindId)
    {
        $exerciseAutoJoinRecordsGroup = ArrayToolkit::group($exerciseAutoJoinRecords, 'userId');
        $singleExerciseAutoJoinRecords = [];
        $multipleExerciseAutoJoinRecords = [];
        foreach ($exerciseAutoJoinRecordsGroup as $userId => $exerciseAutoJoinRecords) {
            $exerciseAutoJoinRecordsGroups = ArrayToolkit::group($exerciseAutoJoinRecords, 'itemBankExerciseId');
            foreach ($exerciseAutoJoinRecordsGroups as $exerciseId => $exerciseAutoJoinRecordsGroup) {
                // 过滤掉当前exerciseBind创建的添加记录
                if (count($exerciseAutoJoinRecordsGroup) > 1) {
                    $filteredRecords = array_filter($exerciseAutoJoinRecordsGroup, function ($record) use ($exerciseBindId) {
                        return $record['exerciseBindId'] != $exerciseBindId;
                    });
                    $multipleExerciseAutoJoinRecords = array_merge($multipleExerciseAutoJoinRecords, $filteredRecords);
                } else {
                    $singleExerciseAutoJoinRecords = array_merge($singleExerciseAutoJoinRecords, $exerciseAutoJoinRecordsGroup);
                }
            }
        }

        return [$singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords];
    }

    public function batchBanLearn($singleExerciseAutoJoinRecords)
    {
        $this->batchUpdateLearnStatus($singleExerciseAutoJoinRecords, 0);
    }

    public function batchCanLearn($singleExerciseAutoJoinRecords)
    {
        $this->batchUpdateLearnStatus($singleExerciseAutoJoinRecords, 1);
    }

    /** 批量更新题库成员学习状态
     * @param $usersWithSingleRecord
     * @param $canLearn
     *
     * @return void
     */
    protected function batchUpdateLearnStatus($singleExerciseAutoJoinRecords, $canLearn)
    {
        $userIds = array_column($singleExerciseAutoJoinRecords, 'userId');
        $exerciseIds = array_column($singleExerciseAutoJoinRecords, 'itemBankExerciseId');
        if (empty($userIds) || empty($exerciseIds)) {
            return;
        }
        $exerciseMembers = $this->getExerciseMemberService()->search(
            ['userIds' => $userIds, 'exerciseId' => $exerciseIds], [], 0, PHP_INT_MAX
        );
        $autoJoinRecordMap = [];
        foreach ($singleExerciseAutoJoinRecords as $record) {
            $autoJoinRecordMap[$record['itemBankExerciseId']][$record['userId']] = true;
        }
        foreach ($exerciseMembers as &$exerciseMember) {
            $exerciseId = $exerciseMember['exerciseId'];
            $userId = $exerciseMember['userId'];
            if (isset($autoJoinRecordMap[$exerciseId][$userId])) {
                $exerciseMember['canLearn'] = $canLearn;
            }
        }
        $exerciseMembers = ArrayToolkit::index($exerciseMembers, 'id');
        $this->getExerciseMemberService()->batchUpdateMembers($exerciseMembers);
    }

    /** 根据学员自动加入题库记录重新计算更新题库学习有效期
     * @param $multipleRecordUsers
     *
     * @return void
     */
    protected function updateMemberExpiredTime($multipleRecordUsers)
    {
        if (empty($multipleRecordUsers)) {
            return;
        }
        $itemBankExerciseBindIds = array_values(array_unique(array_column($multipleRecordUsers, 'itemBankExerciseBindId')));
        $exerciseBinds = $this->getExerciseService()->findBindExerciseByIds($itemBankExerciseBindIds);
        $multipleRecordUsersGroups = ArrayToolkit::group($multipleRecordUsers, 'userId');
        $userIds = array_column($multipleRecordUsers, 'userId');
        $courseIds = array_column(array_filter($exerciseBinds, function ($item) {
            return 'course' == $item['bindType'];
        }), 'bindId');
        $classroomIds = array_column(array_filter($exerciseBinds, function ($item) {
            return 'classroom' == $item['bindType'];
        }), 'bindId');
        if (!empty($courseIds)) {
            $courseIds = $this->getCourseService()->searchCourses(['excludeStatus' => 'closed', 'courseIds' => $courseIds], [], 0, PHP_INT_MAX, ['id']);
            $courseMembers = $this->getCourseMemberService()->searchMembers(['courseIds' => array_column($courseIds, 'id'), 'userIds' => $userIds], [], 0, PHP_INT_MAX);
            $courseMembersGroups = ArrayToolkit::group($courseMembers, 'userId');
        }
        if (!empty($classroomIds)) {
            $classroomIds = $this->getClassroomService()->searchClassrooms(['excludeStatus' => 'closed', 'classroomIds' => $classroomIds], [], 0, PHP_INT_MAX, ['id']);
            $classroomMembers = $this->getClassroomService()->searchMembers(['classroomIds' => array_column($classroomIds, 'id'), 'userIds' => $userIds], [], 0, PHP_INT_MAX);
            $classroomMembersGroups = ArrayToolkit::group($classroomMembers, 'userId');
        }

        $groupedRecords = [];
        foreach ($multipleRecordUsersGroups as $userId => $multipleRecordUsersGroup) {
            if (!empty($courseMembersGroups[$userId])) {
                foreach ($courseMembersGroups[$userId] as $multipleRecord) {
                    if (empty($groupedRecords[$userId])) {
                        $groupedRecords[$userId]['deadline'] = $multipleRecord['deadline'];
                    }
                    if (0 == $multipleRecord['deadline'] || ($multipleRecord['deadline'] > $groupedRecords[$userId]['deadline'] && 0 != $groupedRecords[$userId]['deadline'])) {
                        $groupedRecords[$userId]['deadline'] = $multipleRecord['deadline'];
                    }
                }
            }
            if (!empty($classroomMembersGroups[$userId])) {
                foreach ($classroomMembersGroups[$userId] as $multipleRecord) {
                    if (empty($groupedRecords[$userId])) {
                        $groupedRecords[$userId]['deadline'] = $multipleRecord['deadline'];
                    }
                    if (0 == $multipleRecord['deadline'] || ($multipleRecord['deadline'] > $groupedRecords[$userId]['deadline'] && 0 != $groupedRecords[$userId]['deadline'])) {
                        $groupedRecords[$userId]['deadline'] = $multipleRecord['deadline'];
                    }
                }
            }
        }
        $members = $this->getExerciseMemberService()->search(['exerciseId' => $exerciseBinds[0]['itemBankExerciseId'], 'userIds' => $userIds, 'role' => 'student', 'joinedChannel' => 'bind_join'], [], 0, PHP_INT_MAX);
        foreach ($members as &$member) {
            $member['deadline'] = empty($groupedRecords[$member['userId']]['deadline']) ? 0 : $groupedRecords[$member['userId']]['deadline'];
        }
        if (empty($members)) {
            return;
        }
        $members = ArrayToolkit::index($members, 'id');
        $this->getExerciseMemberService()->batchUpdateMembers($members);
    }

    protected function buildExerciseAutoJoinRecords($userIds, $exerciseBind)
    {
        $exerciseAutoJoinRecords = [];
        foreach ($userIds as $userId) {
            $exerciseAutoJoinRecords[] = [
                'userId' => $userId,
                'itemBankExerciseId' => $exerciseBind['itemBankExerciseId'],
                'itemBankExerciseBindId' => $exerciseBind['id'],
                'isValid' => 1,
            ];
        }
        $exerciseAutoJoinRecords = array_unique(
            array_map('serialize', $exerciseAutoJoinRecords)
        );

        $exerciseAutoJoinRecords = array_map('unserialize', $exerciseAutoJoinRecords);

        return $exerciseAutoJoinRecords;
    }

    protected function getStudentIds($bindType, $bindId)
    {
        if ('course' == $bindType) {
            $member = $this->getCourseMemberService()->findCourseStudents($bindId, 0, PHP_INT_MAX);
        } else {
            $member = $this->getClassroomService()->findClassroomMembersByRole($bindId, 'student', 0, PHP_INT_MAX);
        }

        return array_column($member, 'userId');
    }

    protected function resetExerciseDeadLine($bindType, $bindId, $exercise)
    {
        if ('course' == $bindType) {
            $course = $this->getCourseService()->getCourse($bindId);
            $exercise['expiryMode'] = $course['expiryMode'];
            $exercise['expiryDays'] = $course['expiryDays'];
            $exercise['expiryStartDate'] = $course['expiryStartDate'];
            $exercise['expiryEndDate'] = $course['expiryEndDate'];
        } else {
            $classroom = $this->getClassroomService()->getClassroom($bindId);
            $exercise['expiryMode'] = $classroom['expiryMode'];
            $exercise['expiryDays'] = $classroom['expiryValue'];
        }

        return $exercise;
    }

    protected function filterExistAutoJoinRecords($exerciseAutoJoinRecords, $existingRecords)
    {
        foreach ($exerciseAutoJoinRecords as $key => $record) {
            foreach ($existingRecords as $existingRecord) {
                if (
                    $record['userId'] === $existingRecord['userId'] &&
                    $record['itemBankExerciseId'] === $existingRecord['itemBankExerciseId'] &&
                    $record['itemBankExerciseBindId'] === $existingRecord['itemBankExerciseBindId']
                ) {
                    unset($exerciseAutoJoinRecords[$key]);
                    break;
                }
            }
        }

        return array_values($exerciseAutoJoinRecords);
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
