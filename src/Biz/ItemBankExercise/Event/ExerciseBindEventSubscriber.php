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
        $userIds = $this->getStudentIds($exerciseBind['bindType'], $exerciseBind['bindId']);
        // 查询成员、获取成员IDs
        $autoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId($userIds, $exerciseBind['itemBankExerciseId']);
        list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($autoJoinRecords, $exerciseBind['id']);
        // 只有一条记录直接移除学员
        if (!empty($singleExerciseAutoJoinRecords)) {
            $this->getExerciseMemberService()->batchRemoveStudent($exerciseBind['itemBankExerciseId'], array_column($singleExerciseAutoJoinRecords, 'userId'));
        }
        $multipleExerciseAutoJoinRecords = array_filter($multipleExerciseAutoJoinRecords, function ($record) use ($params) {
            return $record['itemBankExerciseBindId'] != $params['id'];
        });
        // 有多条记录重新计算有效期
        $this->updateMemberExpiredTime($multipleExerciseAutoJoinRecords);
        // 移除自动加入记录
        $this->getExerciseService()->deleteExerciseAutoJoinRecordByExerciseBindId($params['id']);
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
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        $exerciseBindsIndex = ArrayToolkit::index($exerciseBinds, 'itemBankExerciseId');
        $exerciseAutoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByItemBankExerciseBindIds(array_column($exerciseBinds, 'id'));

        $exerciseAutoJoinRecordsGroup = ArrayToolkit::group($exerciseAutoJoinRecords, 'itemBankExerciseId');
        foreach ($exerciseAutoJoinRecordsGroup as $exerciseId => $exerciseAutoJoinRecord) {
            list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($exerciseAutoJoinRecord, $exerciseBindsIndex[$exerciseId]['id']);
            $this->batchBanLearn(array_column($singleExerciseAutoJoinRecords, 'userId'));
            $this->updateMemberExpiredTime($multipleExerciseAutoJoinRecords);
        }
    }

    /** 发布班级/发布课程时，开启学习
     * @return void
     */
    public function onExerciseCanLearn(Event $event)
    {
        $params = $event->getSubject();
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        if (empty($exerciseBinds)) {
            return;
        }
        $exerciseBindsIndex = ArrayToolkit::index($exerciseBinds, 'itemBankExerciseId');
        $exerciseAutoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByItemBankExerciseBindIds(array_column($exerciseBinds, 'id'));

        $exerciseAutoJoinRecordsGroup = ArrayToolkit::group($exerciseAutoJoinRecords, 'itemBankExerciseId');
        foreach ($exerciseAutoJoinRecordsGroup as $exerciseId => $exerciseAutoJoinRecord) {
            list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($exerciseAutoJoinRecord, $exerciseBindsIndex[$exerciseId]['id']);
            $this->batchCanLearn(array_column($singleExerciseAutoJoinRecords, 'userId'));
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
            // 过滤掉当前exerciseBind创建的添加记录
            if (count($exerciseAutoJoinRecords) > 1) {
                $filteredRecords = array_filter($exerciseAutoJoinRecords, function ($record) use ($exerciseBindId) {
                    return $record['exerciseBindId'] != $exerciseBindId;
                });
                $multipleExerciseAutoJoinRecords = array_merge($multipleExerciseAutoJoinRecords, $filteredRecords);
            } else {
                $singleExerciseAutoJoinRecords = array_merge($singleExerciseAutoJoinRecords, $exerciseAutoJoinRecords);
            }
        }

        return [$singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords];
    }

    public function batchBanLearn($usersWithSingleRecord)
    {
        $this->batchUpdateLearnStatus($usersWithSingleRecord, 0);
    }

    public function batchCanLearn($usersWithSingleRecord)
    {
        $this->batchUpdateLearnStatus($usersWithSingleRecord, 1);
    }

    /** 批量更新题库成员学习状态
     * @param $usersWithSingleRecord
     * @param $canLearn
     *
     * @return void
     */
    protected function batchUpdateLearnStatus($usersWithSingleRecord, $canLearn)
    {
        $exerciseMembers = $this->getExerciseMemberService()->search(['userIds' => $usersWithSingleRecord], [], 0, PHP_INT_MAX);
        foreach ($exerciseMembers as &$exerciseMember) {
            $exerciseMember['canLearn'] = $canLearn;
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
            $courseMembers = $this->getCourseMemberService()->searchMembers(['courseIds' => $courseIds, 'userIds' => $userIds], [], 0, PHP_INT_MAX);
            $courseMembersGroups = ArrayToolkit::group($courseMembers, 'userId');
        }
        if (!empty($classroomIds)) {
            $classroomMembers = $this->getClassroomService()->searchMembers(['classroomIds' => $classroomIds, 'userIds' => $userIds], [], 0, PHP_INT_MAX);
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
