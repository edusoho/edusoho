<?php

namespace Biz\ItemBankExercise\Event;

use AppBundle\Common\ArrayToolkit;
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
            'exercise.banLearn' => 'onExerciseBanLearn',
            'exercise.canLearn' => 'onExerciseCanLearn',
        ];
    }

    public function onExerciseBind(Event $event)
    {
        $params = $event->getSubject(); // userIds, bindType, bindId
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        $exerciseBindsIndex = ArrayToolkit::index($exerciseBinds, 'itemBankExerciseId');
        // 判断是否是题库成员
        if (empty($params['userIds'])) {
            return;
        }
        $exerciseUsers = $this->getExerciseMemberService()->search([], [], 0, PHP_INT_MAX);
        $exerciseUsersGroups = ArrayToolkit::group($exerciseUsers, 'exerciseId');
        foreach ($exerciseUsersGroups as $exerciseId => $exerciseUsers) {
            // 分流，已经是成员的ID，不是成员的id
            $exerciseMemberUserIds = array_column($exerciseUsers, 'userId');
            $notMemberUserIds = array_diff($params['userIds'], $exerciseMemberUserIds);
            if (!empty($notMemberUserIds)) {
                $this->getExerciseMemberService()->batchBecomeStudent($exerciseId, $notMemberUserIds, $params['bindType'], $params['bindId']);
            }
            if (!empty($exerciseMemberUserIds)) {
                $this->getExerciseMemberService()->batchUpdateMembers($exerciseId);
            }
            // 重新计算题库有效期
            foreach ($notMemberUserIds as $studentId) {
                $exerciseAutoJoinRecords[] = [
                    'userId' => $studentId,
                    'itemBankExerciseId' => $exerciseId,
                    'itemBankExerciseBindId' => $exerciseBindsIndex[$exerciseId]['bindId'],
                ];
            }
            $this->getItemBankExerciseService()->batchCreateExerciseAutoJoinRecord($exerciseAutoJoinRecords);
        }
    }

    public function onExerciseUnBind(Event $event)
    {
        $params = $event->getSubject(); // userId, bindType, bindId, exerciseId

        // 当userIds为空的时候，解绑绑定关系，移除所有学员
        if (empty($params['userId'])) {
            $autoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByItemBankExerciseBindIds([$params['id']]);
            // 提取所有用户的ID到一个单独的数组中
            $userIds = array_column($autoJoinRecords, 'userId');
            // 统计每个用户的记录数量
            $userRecordCount = array_count_values($userIds);
            // 使用 array_filter 分离出有多条记录和单条记录的用户
            $usersWithMultipleRecords = array_keys(array_filter($userRecordCount, function ($count) {
                return $count > 1;
            }));
            $usersWithSingleRecord = array_keys(array_filter($userRecordCount, function ($count) {
                return 1 == $count;
            }));
            $this->getExerciseMemberService()->batchRemoveStudent(array_column($autoJoinRecords, ''), $usersWithSingleRecord, '', []);
            $this->getExerciseService()->deleteExerciseAutoJoinRecordByUserIdsAndExerciseIds($userIds, array_column($autoJoinRecords, 'itemBankExerciseId'));
        // 需要更新$usersWithMultipleRecords的加入方式
        } else {// 当userIds不为空的时候，就是直接指定解绑某些学员
            $bindExercises = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
            $autoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdAndExerciseIds($params['userId'], array_column($bindExercises, 'itemBankExerciseId'));
            $autoJoinRecordsGroups = ArrayToolkit::group($autoJoinRecords, 'itemBankExerciseId');
            foreach ($autoJoinRecordsGroups as $exerciseId => $autoJoinRecord) {
                $userIds = array_column($autoJoinRecords, 'userId');
                // 统计每个用户的记录数量
                $userRecordCount = array_count_values($userIds);
                // 使用 array_filter 分离出有多条记录和单条记录的用户
                $usersWithMultipleRecords = array_keys(array_filter($userRecordCount, function ($count) {
                    return $count > 1;
                }));
                $usersWithSingleRecord = array_keys(array_filter($userRecordCount, function ($count) {
                    return 1 == $count;
                }));
                $this->getExerciseMemberService()->batchRemoveStudent(array_column($autoJoinRecords, ''), $usersWithSingleRecord, '', []);
                $this->getExerciseService()->deleteExerciseAutoJoinRecordByUserIdsAndExerciseIds($userIds, array_column($autoJoinRecords, 'itemBankExerciseId'));
                // 需要更新$usersWithMultipleRecords的加入方式
            }
        }
    }

    /** 关闭班级/关闭课程时，禁止学习
     * @return void
     */
    public function onExerciseBanLearn(Event $event)
    {
        $params = $event->getSubject();
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        list($usersWithMultipleRecords, $usersWithSingleRecord) = $this->getUsersWithRecordCounts($exerciseBinds);
        $this->batchBanLearn($usersWithSingleRecord);
        $this->updateMemberExpiredTime($usersWithMultipleRecords);
    }

    /** 发布班级/发布课程时，开启学习
     * @return void
     */
    public function onExerciseCanLearn(Event $event)
    {
        $params = $event->getSubject();
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        $exerciseBindsIndex = ArrayToolkit::index($exerciseBinds, 'itemBankExerciseId');
        $exerciseAutoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByItemBankExerciseIds(array_column($exerciseBinds, 'itemBankExerciseId'));

        $exerciseAutoJoinRecordsGroup = ArrayToolkit::group($exerciseAutoJoinRecords, 'itemBankExerciseId');
        foreach ($exerciseAutoJoinRecordsGroup as $exerciseId => $exerciseAutoJoinRecord) {
            list($singleRecordUsers, $multipleRecordUsers) = $this->getUsersWithRecordCounts($exerciseAutoJoinRecord, $exerciseBindsIndex[$exerciseId]['id']);
            $this->batchCanLearn(array_column($singleRecordUsers, 'userId'));
            $this->updateMemberExpiredTime($multipleRecordUsers);
        }
    }

    /** 根据用户查询
     * @param $exerciseBinds
     *
     * @return array
     */
    protected function getUsersWithRecordCounts($exerciseAutoJoinRecords, $exerciseBindId)
    {
        $exerciseAutoJoinRecordsGroup = ArrayToolkit::group($exerciseAutoJoinRecords, 'userId');
        $singleRecordUsers = [];
        $multipleRecordUsers = [];
        foreach ($exerciseAutoJoinRecordsGroup as $userId => $exerciseAutoJoinRecords) {
            if (count($exerciseAutoJoinRecords) > 1) {
                $filteredRecords = array_filter($exerciseAutoJoinRecords, function ($record) use ($exerciseBindId) {
                    return $record['exerciseBindId'] != $exerciseBindId;
                });
                $multipleRecordUsers = array_merge($multipleRecordUsers, $filteredRecords);
            } else {
                $singleRecordUsers = array_merge($singleRecordUsers, $exerciseAutoJoinRecords);
            }
        }

        return [$singleRecordUsers, $multipleRecordUsers];
    }

    public function batchBanLearn($usersWithSingleRecord)
    {
        $this->batchUpdateLearnStatus($usersWithSingleRecord, 0);
    }

    public function batchCanLearn($usersWithSingleRecord)
    {
        $this->batchUpdateLearnStatus($usersWithSingleRecord, 1);
    }

    protected function batchUpdateLearnStatus($usersWithSingleRecord, $canLearn)
    {
        $exerciseMembers = $this->getExerciseMemberService()->search(['userId' => $usersWithSingleRecord], [], 0, PHP_INT_MAX);
        foreach ($exerciseMembers as &$exerciseMember) {
            $exerciseMember['canLearn'] = $canLearn;
        }
        $this->getExerciseMemberService()->batchUpdateMembers($exerciseMembers);
    }

    protected function updateMemberExpiredTime($multipleRecordUsers)
    {
        if (empty($multipleRecordUsers)) {
            return;
        }
        // 先要查询数据的有效期
        $itemBankExerciseBindIds = array_unique(array_column($multipleRecordUsers, 'itemBankExerciseBindId'));
        $exerciseBinds = $this->getExerciseService()->findBindExerciseByIds($itemBankExerciseBindIds);
        $courseIds = array_column(array_filter($exerciseBinds, function ($item) {
            return 'course' == $item['bindType'];
        }), 'bindId');
        $classroomIds = array_column(array_filter($exerciseBinds, function ($item) {
            return 'classroom' == $item['bindType'];
        }), 'bindId');
//        if ()
            // 先根据bindId去重，查询到对应的班级或者课程，查询对应有效期，根据（bindId）做index
            // userId, exerciseBindId, expiredTime
            // 去更新成员信息
        // 查询对应的成员

        // 更新成员有效期
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
}
