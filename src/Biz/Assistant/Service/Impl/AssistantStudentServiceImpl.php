<?php

namespace Biz\Assistant\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\AssistantException;
use Biz\Assistant\Dao\AssistantStudentDao;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Dao\MultiClassGroupDao;
use Biz\MultiClass\Dao\MultiClassRecordDao;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;

class AssistantStudentServiceImpl extends BaseService implements AssistantStudentService
{
    public function create($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['multiClassId', 'studentId', 'assistantId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $fields = $this->filterAssistantStudentFields($fields);

        return $this->getAssistantStudentDao()->create($fields);
    }

    protected function filterAssistantStudentFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            [
                'courseId',
                'studentId',
                'assistantId',
                'multiClassId',
                'group_id',
            ]
        );
    }

    public function updateStudentAssistant($id, $assistantId)
    {
        $user = $this->getUserService()->getUser($assistantId);
        if (empty($user)) {
            throw AssistantException::ASSISTANT_NOT_FOUND();
        }

        $relation = $this->getAssistantStudentDao()->update($id, ['assistantId' => $assistantId]);

        $this->getMultiClassRecordService()->createRecord($relation['studentId'], $relation['multiClassId']);

        return $relation;
    }

    public function get($id)
    {
        return $this->getAssistantStudentDao()->get($id);
    }

    public function delete($id)
    {
        $assistantStudent = $this->get($id);
        if (empty($assistantStudent)) {
            return;
        }

        return $this->getAssistantStudentDao()->delete($id);
    }

    public function getByStudentIdAndMultiClassId($studentId, $multiClassId)
    {
        return $this->getAssistantStudentDao()->getByStudentIdAndMultiClassId($studentId, $multiClassId);
    }

    public function getByStudentIdAndCourseId($studentId, $courseId)
    {
        return $this->getAssistantStudentDao()->getByStudentIdAndCourseId($studentId, $courseId);
    }

    public function findByStudentIdsAndMultiClassId($studentIds, $multiClassId)
    {
        return $this->getAssistantStudentDao()->findByStudentIdsAndMultiClassId($studentIds, $multiClassId);
    }

    public function findRelationsByAssistantIdAndCourseId($assistantId, $courseId)
    {
        return $this->getAssistantStudentDao()->findByAssistantIdAndCourseId($assistantId, $courseId);
    }

    public function findAssistantStudentsByAssistantIdAndMultiClassId($assistantId, $multiClassId)
    {
        return $this->getAssistantStudentDao()->findAssistantStudentsByAssistantIdAndMultiClassId($assistantId, $multiClassId);
    }

    public function findRelationsByMultiClassIdAndStudentIds($multiClassId, $studentIds)
    {
        return $this->getAssistantStudentDao()->findByMultiClassIdAndStudentIds($multiClassId, $studentIds);
    }

    public function findByMultiClassIdAndGroupId($multiClassId, $groupId)
    {
        return $this->getAssistantStudentDao()->findByMultiClassIdAndGroupId($multiClassId, $groupId);
    }

    public function countAssistantStudentGroup($assistantIds, $multiClassIds)
    {
        return ArrayToolkit::index($this->getAssistantStudentDao()->countAssistantStudentGroup($assistantIds, $multiClassIds), 'assistantId');
    }

    public function setGroupAssistantAndStudents($courseId, $multiClassId)
    {
        if (empty($multiClassId) || empty($courseId)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if ('group' != $multiClass['type']) {
            return;
        }

        $roleGroupMemberUserIds = $this->getCourseMemberService()->findGroupUserIdsByCourseIdAndRoles($courseId, ['student', 'assistant']);
        $assistantIds = empty($roleGroupMemberUserIds['assistant']) ? [] : ArrayToolkit::column($roleGroupMemberUserIds['assistant'], 'userId');

        if (empty($assistantIds)) {
            return;
        }

        $groups = $this->getMultiClassGroupDao()->findGroupsByMultiClassId($multiClassId);
        foreach ($groups as $group) {
            if (!in_array($group['assistant_id'], $assistantIds)) {
                $this->getMultiClassGroupDao()->update($group['id'], ['assistant_id' => 0]);
            }
        }

        $unAssignGroups = $this->getMultiClassGroupDao()->findUnAssignGroups($multiClassId);
        $unAssignGroupIds = ArrayToolkit::column($unAssignGroups, 'id');
        $assistantNumGroup = $this->getMultiClassGroupDao()->countMultiClassGroupAssistant($multiClassId);
        $assistantNumGroup = ArrayToolkit::index($assistantNumGroup, 'assistant_id');
        $data = [];
        $this->assignGroups($data, $unAssignGroupIds, $assistantIds, $assistantNumGroup, $multiClass['service_group_num']);
        foreach ($data as $assistantId => $groupIds) {
            foreach ($groupIds as $groupId) {
                $this->getMultiClassGroupDao()->update($groupId, ['assistant_id' => $assistantId]);
                $this->getAssistantStudentDao()->update(['group_id' => $groupId], ['assistantId' => $assistantId]);
            }
        }

        if (!empty($data)) {
            $this->getLogService()->info('group_multi_class_assistant', 'update_assistant_student', '助教和学员变更', $data);
        }
    }

    public function setAssistantStudents($courseId, $multiClassId)
    {
        if (empty($multiClassId) || empty($courseId)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $roleGroupMemberUserIds = $this->getCourseMemberService()->findGroupUserIdsByCourseIdAndRoles($courseId, ['student', 'assistant']);
        $studentIds = empty($roleGroupMemberUserIds['student']) ? [] : ArrayToolkit::column($roleGroupMemberUserIds['student'], 'userId');
        $assistantIds = empty($roleGroupMemberUserIds['assistant']) ? [] : ArrayToolkit::column($roleGroupMemberUserIds['assistant'], 'userId');

        if (empty($assistantIds) || empty($studentIds)) {
            return;
        }

        $multiClassAssistants = $this->getAssistantStudentDao()->findByMultiClassId($multiClassId);
        $existAssistantIds = ArrayToolkit::column($multiClassAssistants, 'assistantId');
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);

        $deleteAssistantIds = array_unique(array_diff($existAssistantIds, $assistantIds));
        if (!empty($deleteAssistantIds)) {
            $this->getAssistantStudentDao()->batchDelete(['assistantIds' => $deleteAssistantIds]);
        }

        $students = $this->getAssistantStudentDao()->findByMultiClassId($multiClassId);
        $noAssistantStudentIds = array_diff($studentIds, ArrayToolkit::column($students, 'studentId'));

        $studentNumGroup = $this->getAssistantStudentDao()->countMultiClassGroupStudent($multiClassId);
        $studentNumGroup = ArrayToolkit::index($studentNumGroup, 'assistantId');

        $data = $result = [];
        $this->assignStudents($data, $noAssistantStudentIds, $assistantIds, $studentNumGroup, $multiClass['service_num']);
        foreach ($data as $assistantId => $studentIds) {
            $fields = [];
            foreach ($studentIds as $studentId) {
                $field['assistantId'] = $assistantId;
                $field['studentId'] = $studentId;
                $field['courseId'] = $courseId;
                $field['multiClassId'] = $multiClassId;
                $fields[] = $field;
            }
            $this->getAssistantStudentDao()->batchCreate($fields);
            $result[] = $fields;
        }

        if (!empty($result)) {
            $this->getLogService()->info('multi_class_assistant', 'update_assistant_student', '助教学员变更', $result);
        }

        return true;
    }

    private function assignGroups(&$data, $unAssignGroupIds, $assistantIds, $assistantNumGroup, $assignNum, $remaining = false)
    {
        foreach ($assistantIds as $assistantId) {
            $assistant = empty($assistantNumGroup[$assistantId]) ? ['groupNum' => 0] : $assistantNumGroup[$assistantId];
            if ($assistant['groupNum'] >= $assignNum) {
                continue;
            }

            $needAssignNum = $assignNum - $assistant['groupNum'];
            $data[$assistantId] = array_slice($unAssignGroupIds, 0, $needAssignNum);
            $unAssignGroupIds = array_diff($unAssignGroupIds, $data[$assistantId]);
        }

        if (!empty($unAssignGroupIds)) {
            $this->assignGroups($data, $unAssignGroupIds, $assistantIds, $assistantNumGroup, $assignNum + 1, true);
        }
    }

    private function assignStudents(&$data, $studentIds, $assistantIds, $studentNumGroup, $average)
    {
        foreach ($assistantIds as $assistantId) {
            $assistant = empty($studentNumGroup[$assistantId]) ? ['studentNum' => 0] : $studentNumGroup[$assistantId];
            if ($assistant['studentNum'] >= $average) {
                continue;
            }

            $needAssignNum = $average - $assistant['studentNum'];
            $data[$assistantId] = array_slice($studentIds, 0, $needAssignNum);
            $studentIds = array_diff($studentIds, $data[$assistantId]);
        }

        if (!empty($studentIds)) {
            $this->assignStudents($data, $studentIds, $assistantIds, $studentNumGroup, $average + 1);
        }
    }

    public function deleteByStudentIdAndCourseId($studentId, $courseId)
    {
        return $this->getAssistantStudentDao()->deleteByStudentIdAndCourseId($studentId, $courseId);
    }

    public function findByMultiClassId($multiClassId)
    {
        return $this->getAssistantStudentDao()->findByMultiClassId($multiClassId);
    }

    public function findByMultiClassIds($multiClassIds)
    {
        return $this->getAssistantStudentDao()->findByMultiClassIds($multiClassIds);
    }

    public function filterAssistantConditions($conditions, $courseId)
    {
        $user = $this->getCurrentUser();
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);
        if ('assistant' != $member['role']) {
            return $conditions;
        }

        $assistantStudentRef = $this->findRelationsByAssistantIdAndCourseId($user['id'], $courseId);
        if (empty($assistantStudentRef)) {
            $conditions['userIds'] = [-1];

            return $conditions;
        }

        $studentIds = ArrayToolkit::column($assistantStudentRef, 'studentId');
        $conditions['userIds'] = !empty($conditions['userIds']) ? array_intersect($studentIds, $conditions['userIds']) : $studentIds;

        return $conditions;
    }

    public function batchUpdateStudentsGroup($multiClassId, $studentIds, $groupId)
    {
        try {
            $this->beginTransaction();

            $originRelations = $this->findByStudentIdsAndMultiClassId($studentIds, $multiClassId);
            $originRelations = ArrayToolkit::index($originRelations, 'studentId');
            $this->updateMultiClassStudentsGroup(ArrayToolkit::column($originRelations, 'id'), $groupId);
            $this->batchCreateRecords($multiClassId, $studentIds, $originRelations);
            $this->batchUpdateGroupStudentNum($multiClassId, array_merge([$groupId], ArrayToolkit::column($originRelations, 'group_id')));

            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchUpdateStudentsGroup:'.$e->getMessage(), ['multiClassId' => $multiClassId, 'studentIds' => $studentIds, 'groupId' => $groupId]);
            $this->rollback();
            throw $e;
        }
    }

    private function updateMultiClassStudentsGroup($assistantStudentIds, $groupId)
    {
        $multiClassGroup = $this->getMultiClassGroupService()->getMultiClassGroup($groupId);
        $fields = [];
        foreach ($assistantStudentIds as $assistantStudentId) {
            $fields[] = [
                'id' => $assistantStudentId,
                'group_id' => $groupId,
                'assistantId' => $multiClassGroup['assistant_id'],
            ];
        }

        $this->getAssistantStudentDao()->batchUpdate($assistantStudentIds, $fields);
    }

    private function batchUpdateGroupStudentNum($multiClassId, $groupIds)
    {
        $groupIds = array_values(array_unique($groupIds));
        $groupStudentNum = $this->getAssistantStudentDao()->countMultiClassGroupStudentByGroupIds($multiClassId, $groupIds);
        $groupStudentNum = ArrayToolkit::index($groupStudentNum, 'groupId');

        $updateRecords = [];
        $deleteGroupIds = [];
        foreach ($groupIds as $key => $groupId) {
            if (empty($groupStudentNum[$groupId]['studentNum'])) {
                $deleteGroupIds[] = $groupId;
                unset($groupIds[$key]);
                continue;
            }
            $updateRecords[] = ['student_num' => $groupStudentNum[$groupId]['studentNum']];
        }
        $this->getMultiClassGroupService()->batchDeleteMultiClassGroups($deleteGroupIds);

        return $this->getMultiClassGroupDao()->batchUpdate($groupIds, $updateRecords);
    }

    private function batchCreateRecords($multiClassId, $studentIds, $originRelations)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        $currentRelations = $this->findByStudentIdsAndMultiClassId($studentIds, $multiClassId);
        $currentRelations = ArrayToolkit::index($currentRelations, 'studentId');
        $assistants = $this->getUserService()->findUsersByIds(ArrayToolkit::column($currentRelations, 'assistant_id'));
        $groups = $this->getMultiClassGroupService()->findGroupsByIds(ArrayToolkit::column($currentRelations, 'group_id'));

        $records = [];
        foreach ($studentIds as $studentId) {
            $relation = $currentRelations[$studentId];
            $assistant = $assistants[$currentRelations[$studentId]['assistantId']];
            if (empty($groups[$relation['group_id']])) {
                $content = sprintf('加入班课(%s), 分配助教(%s)', $multiClass['title'], $assistant['nickname']);
            } else {
                $group = $groups[$relation['group_id']];
                $content = sprintf('加入班课(%s)的%s, 分配助教(%s)', $multiClass['title'], MultiClassGroupService::MULTI_CLASS_GROUP_NAME.$group['seq'], $assistant['nickname']);
            }
            $records[] = [
                'user_id' => $studentId,
                'assistant_id' => $currentRelations[$studentId]['assistantId'],
                'multi_class_id' => $multiClassId,
                'data' => ['title' => '加入班课', 'content' => $content],
                'sign' => $this->getMultiClassRecordService()->makeSign(),
                'is_push' => 0,
            ];
        }

        return $this->getMultiClassRecordDao()->batchCreate($records);
    }

    public function findAssistantStudentsByGroupIds($groupIds)
    {
        return $this->getAssistantStudentDao()->findAssistantStudentsByGroupIds($groupIds);
    }

    public function deleteByMultiClassId($multiClassId)
    {
        if ($multiClassId) {
            $this->getAssistantStudentDao()->batchDelete(['multiClassId' => $multiClassId]);
        }
    }

    /**
     * @return MultiClassGroupDao
     */
    private function getMultiClassGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassGroupDao');
    }

    /**
     * @return MultiClassRecordDao
     */
    private function getMultiClassRecordDao()
    {
        return $this->createDao('MultiClass:MultiClassRecordDao');
    }

    /**
     * @return MultiClassRecordService
     */
    private function getMultiClassRecordService()
    {
        return $this->createService('MultiClass:MultiClassRecordService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return AssistantStudentDao
     */
    protected function getAssistantStudentDao()
    {
        return $this->createDao('Assistant:AssistantStudentDao');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }

    /**
     * @return MultiClassGroupService
     */
    protected function getMultiClassGroupService()
    {
        return $this->createService('MultiClass:MultiClassGroupService');
    }
}
