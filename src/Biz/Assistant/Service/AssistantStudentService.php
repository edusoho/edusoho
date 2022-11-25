<?php

namespace Biz\Assistant\Service;

interface AssistantStudentService
{
    public function setAssistantStudents($courseId, $multiClassId);

    public function setGroupAssistantAndStudents($courseId, $multiClassId);

    public function getByStudentIdAndMultiClassId($studentId, $multiClassId);

    public function getByStudentIdAndCourseId($studentId, $courseId);

    public function findByStudentIdsAndMultiClassId($studentIds, $multiClassId);

    public function findRelationsByAssistantIdAndCourseId($assistantId, $courseId);

    public function findAssistantStudentsByAssistantIdAndMultiClassId($assistantId, $multiClassId);

    public function findRelationsByMultiClassIdAndStudentIds($multiClassId, $studentIds);

    public function create($fields);

    public function updateStudentAssistant($id, $assistantId);

    public function delete($id);

    public function get($id);

    public function filterAssistantConditions($conditions, $courseId);

    public function deleteByStudentIdAndCourseId($studentId, $courseId);

    public function findByMultiClassId($multiClassId);

    public function findByMultiClassIds($multiClassIds);

    public function batchUpdateStudentsGroup($multiClassId, $studentIds, $groupId);

    public function findByMultiClassIdAndGroupId($multiClassId, $groupId);

    public function countAssistantStudentGroup($assistantIds, $multiClassIds);

    public function findAssistantStudentsByGroupIds($groupIds);

    public function deleteByMultiClassId($multiClassId);
}
