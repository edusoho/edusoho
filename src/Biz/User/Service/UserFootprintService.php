<?php

namespace Biz\User\Service;

interface UserFootprintService
{
    const PREPARE_METHODS = [
        'task' => 'prepareTaskFootprints',
        'item_bank_assessment_exercise' => 'prepareItemBankAssessmentExerciseFootprints',
        'item_bank_chapter_exercise' => 'prepareItemBankChapterExerciseFootprints',
    ];

    public function createUserFootprint($footprint);

    public function updateFootprint($id, $footprint);

    public function searchUserFootprints(array $conditions, array $order, $start, $limit, $columns = []);

    public function countUserFootprints($conditions);

    public function prepareUserFootprintsByType($footprints, $type);

    public function deleteUserFootprintsBeforeDate($date);
}
