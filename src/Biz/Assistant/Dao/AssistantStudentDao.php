<?php

namespace Biz\Assistant\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AssistantStudentDao extends AdvancedDaoInterface
{
    public function countMultiClassGroupStudent($multiClassId);

    public function countMultiClassGroupStudentByGroupIds($multiClassId, $groupIds);

    public function getByStudentIdAndMultiClassId($studentId, $multiClassId);

    public function getByStudentIdAndCourseId($studentId, $courseId);

    public function findByStudentIdsAndMultiClassId($studentIds, $multiClassId);

    public function findByAssistantIdAndCourseId($assistantId, $courseId);

    public function findByMultiClassIdAndStudentIds($multiClassId, $studentIds);

    public function deleteByStudentIdAndCourseId($studentId, $courseId);

    public function findByMultiClassId($multiClassId);

    public function findByMultiClassIds($multiClassIds);

    public function updateMultiClassStudentsGroup($multiClassId, $conditions);

    public function findByMultiClassIdAndGroupId($multiClassId, $groupId);
}
