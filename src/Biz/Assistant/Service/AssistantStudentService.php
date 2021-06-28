<?php

namespace Biz\Assistant\Service;

interface AssistantStudentService
{
    public function setAssistantStudents($courseId, $multiClassId);

    public function getByStudentIdAndMultiClassId($studentId, $multiClassId);

    public function findRelationsByAssistantIdAndCourseId($assistantId, $courseId);

    public function findRelationsByMultiClassIdAndStudentIds($multiClassId, $studentIds);

    public function create($fields);

    public function update($id, $fields);

    public function filterAssistantConditions($conditions, $courseId);
}
