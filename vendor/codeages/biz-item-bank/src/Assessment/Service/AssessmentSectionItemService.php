<?php

namespace Codeages\Biz\ItemBank\Assessment\Service;

interface AssessmentSectionItemService
{
    public function getAssessmentSectionItem($id);

    public function findSectionItemsByAssessmentId($assessmentId);

    public function findSectionItemDetailByAssessmentId($assessmentId);

    public function createAssessmentSectionItem($item, $section);

    public function updateAssessmentSectionItem($id, $fields);

    public function deleteAssessmentSectionItem($id);

    public function deleteAssessmentSectionItemsByAssessmentId($assessmentId);

    public function countAssessmentSectionItems($conditions);

    public function searchAssessmentSectionItems($conditions, $orderBys, $start, $limit, $columns = array());
}
