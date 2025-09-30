<?php

namespace Biz\ItemBankExercise\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class ExerciseBindJob extends AbstractJob
{
    public function execute()
    {
        $bindType = $this->args['bindType'];
        $bindId = $this->args['bindId'];
        $userIds = $this->getStudentIds($bindType, $bindId);
        if (empty($userIds)) {
            return;
        }
        foreach ($this->args['exerciseBinds'] as $exerciseBind) {
            // 查询学员是不是当前题库练习的成员
            $exerciseUsers = $this->getExerciseMemberService()->search(['userIds' => $userIds, 'exerciseId' => $exerciseBind['itemBankExerciseId'], 'role' => 'student'], [], 0, PHP_INT_MAX);
            // 拆分是题库成员的部分，不是题库成员的部分
            $exerciseMemberUserIds = array_column($exerciseUsers, 'userId');
            $notMemberUserIds = array_diff($userIds, $exerciseMemberUserIds);
            $exercise = $this->getExerciseService()->get($exerciseBind['itemBankExerciseId']);
            $exercise = $this->resetExerciseDeadLine($bindType, $bindId, $exercise);
            if (!empty($notMemberUserIds)) {
                $this->getExerciseMemberService()->batchBecomeStudent([$exerciseBind['itemBankExerciseId']], $notMemberUserIds, ['joinedChannel' => 'bind_join'], $exercise);
            }
            if (!empty($exerciseUsers)) {
                $exerciseAutoJoinRecords = $this->buildExerciseAutoJoinRecords(array_column($exerciseUsers, 'userId'), $exerciseBind);
                $this->updateMemberExpiredTime($exerciseAutoJoinRecords);
            }
            $exerciseAutoJoinRecords = $this->buildExerciseAutoJoinRecords($userIds, $exerciseBind);
            $existingRecords = $this->getExerciseService()->findExerciseAutoJoinRecordByUserIdsAndExerciseIdAll($userIds, $exerciseBind['itemBankExerciseId']);
            $exerciseAutoJoinRecords = $this->filterExistAutoJoinRecords($exerciseAutoJoinRecords, $existingRecords);
            $this->getItemBankExerciseService()->batchCreateExerciseAutoJoinRecord($exerciseAutoJoinRecords);
            $exerciseBind['status'] = 'finished';
            $this->getExerciseService()->updateBindExercise($exerciseBind);
        }
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

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
