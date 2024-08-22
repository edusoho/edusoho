<?php

namespace Codeages\Biz\ItemBank\Assessment\Service;

interface AssessmentSectionItemService
{
    public function getAssessmentSectionItem($id);

    public function getItemByAssessmentIdAndItemId($assessmentId, $itemId);

    public function findSectionItemsByAssessmentId($assessmentId);

    public function findSectionItemDetailByAssessmentId($assessmentId);

    public function createAssessmentSectionItem($item, $section);

    public function updateAssessmentSectionItem($id, $fields);

    public function deleteAssessmentSectionItem($id);

    public function deleteAssessmentSectionItemsByAssessmentId($assessmentId);

    public function deleteAssessmentSectionItemsByAssessmentIds($assessmentIds);

    public function countAssessmentSectionItems($conditions);

    public function searchAssessmentSectionItems($conditions, $orderBys, $start, $limit, $columns = array());

    public function findSectionItemsByAssessmentIds($assessmentIds);

    public function createAssessmentSectionItems($items);

    public function deleteAssessmentSectionItems($toDeleteSectionItems);

    public function findDeletedAssessmentSectionItems($assessmentId);
}
