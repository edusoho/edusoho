<?php

namespace Codeages\Biz\ItemBank\Assessment\Service;

interface AssessmentSectionService
{
    public function getAssessmentSection($id);

    public function findSectionsByAssessmentId($assessmentId);

    public function findSectionDetailByAssessmentId($assessmentId);

    public function createAssessmentSection($assessmentId, $section);

    public function updateAssessmentSection($id, $fields);

    public function deleteAssessmentSection($id);

    public function deleteAssessmentSectionsByAssessmentId($assessmentId);

    public function countAssessmentSections($conditions);

    public function searchAssessmentSections($conditions, $orderBys, $start, $limit, $columns = array());
}
