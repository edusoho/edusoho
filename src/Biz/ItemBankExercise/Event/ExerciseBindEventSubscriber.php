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
            'exercise.bind.add.student' => 'on1ExerciseBindAddStudent',
            'exercise.bind.remove.student' => 'on2ExerciseBindRemoveStudent',
            'exercise.banLearn' => 'onExerciseBanLearn',
            'exercise.canLearn' => 'onExerciseCanLearn',
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
            $exerciseUsers = $this->getExerciseMemberService()->search(['userIds' => $userIds, 'exerciseIds' => array_column($params['exerciseBinds'], 'itemBankExerciseId')], [], 0, PHP_INT_MAX);
            // 拆分是题库成员的部分，不是题库成员的部分
            $exerciseMemberUserIds = array_column($exerciseUsers, 'userId');
            $notMemberUserIds = array_diff($userIds, $exerciseMemberUserIds);
            $exercise = $this->getExerciseService()->get($exerciseBind['itemBankExerciseId']);
            $exercise = $this->resetExerciseDeadLine($params['bindType'], $params['bindId'], $exercise);
            if (!empty($notMemberUserIds)) {
                $this->getExerciseMemberService()->batchBecomeStudent([$exerciseBind['itemBankExerciseId']], $notMemberUserIds, '', $exercise);
            }
            if (!empty($exerciseUsers)) {
                $exerciseAutoJoinRecords = $this->buildExerciseAutoJoinRecords(array_column($exerciseUsers, 'userId'), $exerciseBind);
                $this->updateMemberExpiredTime($exerciseAutoJoinRecords);
            }
            $exerciseAutoJoinRecords = $this->buildExerciseAutoJoinRecords($userIds, $exerciseBind);
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
        // 查询成员、获取成员IDs
        $autoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseId($params['userIds'], $exerciseBind['itemBankExerciseId']);
        list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($autoJoinRecords, $exerciseBind['id']);
        // 只有一条记录直接移除学员
        $this->getExerciseMemberService()->batchRemoveStudent($exerciseBind['itemBankExerciseId'], array_column($singleExerciseAutoJoinRecords, 'userId'));
        // 有多条记录重新计算有效期
        $this->updateMemberExpiredTime($multipleExerciseAutoJoinRecords);
        // 移除自动加入记录
        $this->getExerciseService()->deleteExerciseAutoJoinRecordByExerciseBindId($params['id']);
    }

    /** 课程/班级添加学员
     * @return void
     */
    public function onExerciseBindAddStudent(Event $event)
    {
        $params = $event->getSubject();
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
    }

    /** 课程/班级移除学员
     * @return void
     */
    public function onExerciseBindRemoveStudent(Event $event)
    {
        $params = $event->getSubject();
        $exerciseBinds = $this->getExerciseService()->findBindExercise($params['bindType'], $params['bindId']);
        $exerciseBindsIndex = ArrayToolkit::index($exerciseBinds, 'itemBankExerciseId');
        $exerciseAutoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByItemBankExerciseBindIds(array_column($exerciseBinds, 'id'));
        $userIds = array_column($exerciseAutoJoinRecords, 'userId');
        $exerciseAutoJoinRecordsGroup = ArrayToolkit::group($exerciseAutoJoinRecords, 'itemBankExerciseId');
        foreach ($exerciseAutoJoinRecordsGroup as $exerciseId => $exerciseAutoJoinRecord) {
            list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($exerciseAutoJoinRecord, $exerciseBindsIndex[$exerciseId]['id']);
            $this->getExerciseMemberService()->batchRemoveStudent($exerciseId, array_column($singleExerciseAutoJoinRecords, 'userId'));
            $this->updateMemberExpiredTime($multipleExerciseAutoJoinRecords);
        }
        $this->getExerciseService()->deleteExerciseAutoJoinRecordByUserIdsAndExerciseIds($userIds, array_column($exerciseId, 'itemBankExerciseId'));
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
        $exerciseBindsIndex = ArrayToolkit::index($exerciseBinds, 'itemBankExerciseId');
        $exerciseAutoJoinRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByItemBankExerciseBindIds(array_column($exerciseBinds, 'id'));

        $exerciseAutoJoinRecordsGroup = ArrayToolkit::group($exerciseAutoJoinRecords, 'itemBankExerciseId');
        foreach ($exerciseAutoJoinRecordsGroup as $exerciseId => $exerciseAutoJoinRecord) {
            list($singleExerciseAutoJoinRecords, $multipleExerciseAutoJoinRecords) = $this->categorizeUserRecordsByCount($exerciseAutoJoinRecord, $exerciseBindsIndex[$exerciseId]['id']);
            $this->batchCanLearn(array_column($singleExerciseAutoJoinRecords, 'userId'));
            $this->updateMemberExpiredTime($multipleExerciseAutoJoinRecords);
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
        $exerciseMembers = $this->getExerciseMemberService()->search(['userId' => $usersWithSingleRecord], [], 0, PHP_INT_MAX);
        foreach ($exerciseMembers as &$exerciseMember) {
            $exerciseMember['canLearn'] = $canLearn;
        }
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
        // 先要查询数据的有效期
        $itemBankExerciseBindIds = array_unique(array_column($multipleRecordUsers, 'itemBankExerciseBindId'));
        $exerciseBinds = $this->getExerciseService()->findBindExerciseByIds($itemBankExerciseBindIds);
        $exerciseBindsIndex = ArrayToolkit::index($exerciseBinds, 'id');
        $courseIds = array_column(array_filter($exerciseBinds, function ($item) {
            return 'course' == $item['bindType'];
        }), 'bindId');
        $classroomIds = array_column(array_filter($exerciseBinds, function ($item) {
            return 'classroom' == $item['bindType'];
        }), 'bindId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $coursesIndex = ArrayToolkit::index($courses, 'id');
        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
        $classroomsIndex = ArrayToolkit::index($classrooms, 'id');
        foreach ($multipleRecordUsers as &$multipleRecord) {
            $exerciseBind = $exerciseBindsIndex[$multipleRecord['itemBankExerciseBindId']];
            if ('course' == $exerciseBind['bindType']) {
                $multipleRecord['deadline'] = $coursesIndex[$exerciseBind['bindId']]['expiredTime'];
            } else {
                $multipleRecord['deadline'] = $classroomsIndex[$exerciseBind['bindId']]['expiredTime'];
            }
        }
        $groupedRecords = [];

        foreach ($multipleRecordUsers as $record) {
            // 定义一个唯一键来标识每组
            $key = $record['exerciseId'].'-'.$record['userId'];
            // 如果该组已经存在，检查是否需要更新记录
            if (isset($groupedRecords[$key])) {
                // 如果当前记录的 deadline 为 0 或者大于已存在的 deadline
                if (0 == $record['deadline'] || ($record['deadline'] > $groupedRecords[$key]['deadline'] && 0 != $groupedRecords[$key]['deadline'])) {
                    // 更新记录
                    $groupedRecords[$key] = $record;
                }
            } else {
                // 新增组
                $groupedRecords[$key] = $record;
            }
        }
        $exerciseIds = [];
        $userIds = [];
        foreach ($groupedRecords as $groupedRecord) {
            $exerciseIds[] = $groupedRecord['exerciseId'];
            $userIds[] = $groupedRecord['userId'];
        }
        $members = $this->getExerciseMemberService()->search(['exerciseIds' => $exerciseIds, 'userIds' => $userIds], [], 0, PHP_INT_MAX);
        $memberMap = [];
        foreach ($members as $member) {
            $key = $member['exerciseId'].'_'.$member['userId'];
            $memberMap[$key] = $member;
        }
        foreach ($groupedRecords as $groupedRecord) {
            $key = $groupedRecord['exerciseId'].'_'.$groupedRecord['userId'];
            if (isset($memberMap[$key])) {
                $member = $memberMap[$key];
                $member['deadline'] = $groupedRecord['deadline'];
            }
        }
        if (empty($members)) {
            return;
        }
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
        } else {
            $classroom = $this->getClassroomService()->getClassroom($bindId);
            $exercise['expiryMode'] = $classroom['expiryMode'];
            $exercise['expiryDays'] = $classroom['expiryValue'];
        }

        return $exercise;
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
