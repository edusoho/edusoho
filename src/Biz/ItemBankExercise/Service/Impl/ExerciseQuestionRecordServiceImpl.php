<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\ItemBankExercise\Service\ExerciseQuestionRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;

class ExerciseQuestionRecordServiceImpl extends BaseService implements ExerciseQuestionRecordService
{
    public function findByUserIdAndExerciseId($userId, $exerciseId)
    {
        return $this->getItemBankExerciseQuestionRecordDao()->findByUserIdAndExerciseId($userId, $exerciseId);
    }

    public function batchCreate($questionRecords)
    {
        return $this->getItemBankExerciseQuestionRecordDao()->batchCreate($questionRecords);
    }

    public function batchUpdate($ids, $questionRecords)
    {
        return $this->getItemBankExerciseQuestionRecordDao()->batchUpdate($ids, $questionRecords);
    }

    public function deleteByQuestionIds(array $questionIds)
    {
        return $this->getItemBankExerciseQuestionRecordDao()->batchDelete(['questionIds' => $questionIds]);
    }

    public function deleteByItemIds(array $itemIds)
    {
        return $this->getItemBankExerciseQuestionRecordDao()->batchDelete(['itemIds' => $itemIds]);
    }

    public function updateByAnswerRecordIdAndModuleId($answerRecordId, $moduleId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($answerRecord)) {
            return;
        }

        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (empty($module)) {
            return;
        }

        $questionRecords = ArrayToolkit::index($this->findByUserIdAndExerciseId($answerRecord['user_id'], $module['exerciseId']), 'questionId');
        $answerQuestionReports = $this->getAnswerQuestionReports($answerRecord['answer_report_id']);

        $updateRecords = [];
        $createRecords = [];
        foreach ($answerQuestionReports as $answerQuestionReport) {
            if (empty($questionRecords[$answerQuestionReport['questionId']])) {
                $createRecords[] = [
                    'exerciseId' => $module['exerciseId'],
                    'userId' => $answerRecord['user_id'],
                    'answerRecordId' => $answerRecordId,
                    'moduleType' => $module['type'],
                    'itemId' => $answerQuestionReport['itemId'],
                    'questionId' => $answerQuestionReport['questionId'],
                    'status' => $answerQuestionReport['status'],
                ];
            } else {
                $updateRecords[] = [
                    'id' => $questionRecords[$answerQuestionReport['questionId']]['id'],
                    'status' => $answerQuestionReport['status'],
                    'answerRecordId' => $answerRecordId,
                    'moduleType' => $module['type'],
                ];
            }
        }

        !empty($updateRecords) && $this->batchUpdate(ArrayToolkit::column($updateRecords, 'id'), $updateRecords);
        !empty($createRecords) && $this->batchCreate($createRecords);
    }

    protected function getAnswerQuestionReports($answerReportId)
    {
        $answerReport = $this->getAnswerReportService()->get($answerReportId);

        $answerQuestionReports = [];
        foreach ($answerReport['section_reports'] as $sectionReport) {
            foreach ($sectionReport['item_reports'] as $itemReport) {
                foreach ($itemReport['question_reports'] as $questionReport) {
                    if (!in_array($questionReport['status'], [AnswerQuestionReportService::STATUS_NOANSWER])) {
                        $answerQuestionReports[] = [
                            'itemId' => $itemReport['item_id'],
                            'questionId' => $questionReport['question_id'],
                            'status' => $questionReport['status'],
                        ];
                    }
                }
            }
        }

        return $answerQuestionReports;
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }

    protected function getItemBankExerciseQuestionRecordDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseQuestionRecordDao');
    }
}
