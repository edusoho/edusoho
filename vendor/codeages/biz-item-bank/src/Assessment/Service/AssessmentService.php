<?php

namespace Codeages\Biz\ItemBank\Assessment\Service;

interface AssessmentService
{
    const OPEN = 'open';

    const DRAFT = 'draft';

    const CLOSED = 'closed';

    public function getAssessment($id);

    public function findAssessmentsByIds($assessmentIds);

    public function showAssessment($assessmentId);

    public function createAssessment($assessment);

    public function createBasicAssessment($assessment);

    public function importAssessment($assessment);

    public function updateAssessment($id, $assessment);

    public function updateBasicAssessment($assessmentId, $assessment);

    public function deleteAssessment($id);

    public function drawItems($range, $sections);

    public function countAssessments($conditions);

    public function searchAssessments($conditions, $orderBys, $start, $limit, $columns = array());

    public function openAssessment($id);

    public function closeAssessment($id);

    public function review($assessmentId, $sectionResponses);

    public function exportAssessment($assessmentId, $path, $imgRootDir);

    public function findAssessmentQuestions($assessmentId);
}
