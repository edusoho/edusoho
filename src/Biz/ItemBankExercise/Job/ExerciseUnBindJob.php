<?php

namespace Biz\ItemBankExercise\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ExerciseUnBindJob extends AbstractJob
{
    public function execute()
    {
        $bindType = $this->args['bindType'];
        $bindId = $this->args['bindId'];
        $exerciseBind = $this->args['exerciseBind'];
        if (empty($exerciseBind)) {
            $exerciseBinds = $this->getExerciseService()->findBindExercise($bindType, $bindId);
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
        $this->getExerciseService()->deleteExerciseBind($exerciseBind['id']);
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

    protected function getStudentIds($bindType, $bindId)
    {
        if ('course' == $bindType) {
            $member = $this->getCourseMemberService()->findCourseStudents($bindId, 0, PHP_INT_MAX);
        } else {
            $member = $this->getClassroomService()->findClassroomMembersByRole($bindId, 'student', 0, PHP_INT_MAX);
        }

        return array_column($member, 'userId');
    }

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

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
