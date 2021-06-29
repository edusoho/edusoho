<?php

namespace Biz\Assistant\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\Dao\AssistantStudentDao;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;

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

    public function update($id, $fields)
    {
        $fields = $this->filterAssistantStudentFields($fields);

        return $this->getAssistantStudentDao()->update($id, $fields);
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
            ]
        );
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

    public function findRelationsByMultiClassIdAndStudentIds($multiClassId, $studentIds)
    {
        return $this->getAssistantStudentDao()->findByMultiClassIdAndStudentIds($multiClassId, $studentIds);
    }

    public function setAssistantStudents($courseId, $multiClassId)
    {
        if (empty($multiClassId) || empty($courseId)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $assistants = $this->getMemberService()->searchMembers(['courseId' => $courseId, 'role' => 'assistant'], [], 0, PHP_INT_MAX, ['userId']);
        $assistantIds = ArrayToolkit::column($assistants, 'userId');

        if (empty($assistantIds)) {
            return;
        }

        $multiClassAssistants = $this->getAssistantStudentDao()->search(['multiClassId' => $multiClassId], [], 0, PHP_INT_MAX, ['assistantId']);
        $existAssistantIds = ArrayToolkit::column($multiClassAssistants, 'assistantId');

        $deleteAssistantIds = array_diff($existAssistantIds, $assistantIds);
        if (!empty($deleteAssistantIds)) {
            $this->getAssistantStudentDao()->batchDelete(['assistantIds' => $deleteAssistantIds]);
        }

        $students = $this->getAssistantStudentDao()->search(['multiClassId' => $multiClassId], [], 0, PHP_INT_MAX, ['studentId']);
        $courseMembers = $this->getMemberService()->searchMembers(['courseId' => $courseId, 'role' => 'student'], [], 0, PHP_INT_MAX, ['userId']);
        $noAssistantStudentIds = array_diff(ArrayToolkit::column($courseMembers, 'userId'), ArrayToolkit::column($students, 'studentId'));

        $studentNumGroup = $this->getAssistantStudentDao()->countMultiClassGroupStudent($multiClassId);
        $studentNumGroup = ArrayToolkit::index($studentNumGroup, 'assistantId');

        $averageStudentNum = floor(count($courseMembers) / count($assistantIds));

        $data = [];
        $this->assign($data, $noAssistantStudentIds, $assistantIds, $studentNumGroup, $averageStudentNum);

        $result = [];
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
            $this->getLogService()->info('multi_class_assistant', 'update_assistant_student', '调整助教学员', $result);
        }

        return true;
    }

    private function assign(&$data, $studentIds, $assistantIds, $studentNumGroup, $average, $remaining = false)
    {
        foreach ($assistantIds as $assistantId) {
            $assistant = empty($studentNumGroup[$assistantId]) ? ['studentNum' => 0] : $studentNumGroup[$assistantId];

            if ($remaining) {
                $data[$assistantId] = array_merge($data[$assistantId] ?: [], array_slice($studentIds, 0, $average));
                $studentIds = array_diff($studentIds, $data[$assistantId]);
                continue;
            }

            if ($assistant['studentNum'] >= $average) {
                continue;
            }

            $needAssignNum = $average - $assistant['studentNum'];
            $data[$assistantId] = array_slice($studentIds, 0, $needAssignNum);
            $studentIds = array_diff($studentIds, $data[$assistantId]);
        }

        if (!empty($studentIds)) {
            $this->assign($data, $studentIds, $assistantIds, $studentNumGroup, 1, true);
        }
    }

    public function filterAssistantConditions($conditions, $courseId)
    {
        $user = $this->getCurrentUser();
        $member = $this->getMemberService()->getCourseMember($courseId, $user['id']);
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
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
}
