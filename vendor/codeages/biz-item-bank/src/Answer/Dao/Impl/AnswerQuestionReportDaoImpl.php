<?php
namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerQuestionReportDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AnswerQuestionReportDaoImpl extends AdvancedDaoImpl implements AnswerQuestionReportDao
{
    protected $table = 'biz_answer_question_report';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function deleteByAssessmentId($assessmentId)
    {
        $sql = "DELETE FROM {$this->table} WHERE assessment_id = ?";

        return $this->db()->executeUpdate($sql, [$assessmentId]);
    }

    public function findByAnswerRecordId($answerRecordId)
    {
        return $this->findByFields(['answer_record_id' => $answerRecordId]);
    }

    public function getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId)
    {
        return $this->getByFields([
            'answer_record_id' => $answerRecordId,
            'question_id' => $questionId
        ]);
    }

    public function batchUpdateByTwoIdentify($caseIdentifies, $updateColumnsList, $caseIdentifyColumn, $whereIdentifyColumn, $whereIdentifies)
    {
        $updateColumns = array_keys(reset($updateColumnsList));

        $this->db()->checkFieldNames($updateColumns);
        $this->db()->checkFieldNames([$whereIdentifyColumn]);
        $this->db()->checkFieldNames([$caseIdentifyColumn]);

        $pageSize = 500;
        $pageCount = ceil(count($caseIdentifies) / $pageSize);

        for ($i = 1; $i <= $pageCount; ++$i) {
            $start = ($i - 1) * $pageSize;
            $partCaseIdentifies = array_slice($caseIdentifies, $start, $pageSize);
            $partUpdateColumnsList = array_slice($updateColumnsList, $start, $pageSize);
            $this->partUpdate($whereIdentifyColumn, $whereIdentifies, $caseIdentifyColumn, $partCaseIdentifies, $partUpdateColumnsList, $updateColumns);
        }
    }

    private function partUpdate($whereIdentifyColumn, $whereIdentifies, $caseIdentifyColumn, $caseIdentifies, $updateColumnsList, $updateColumns)
    {
        $sql = "UPDATE {$this->table} SET ";

        $updateSql = [];

        $params = [];
        foreach ($updateColumns as $updateColumn) {
            $caseWhenSql = "{$updateColumn} = CASE {$caseIdentifyColumn} ";

            foreach ($caseIdentifies as $identifyIndex => $caseIdentify) {
                $caseWhenSql .= ' WHEN ? THEN ? ';
                $params[] = $caseIdentify;
                $params[] = $updateColumnsList[$identifyIndex][$updateColumn];
                if ($identifyIndex === count($caseIdentifies) - 1) {
                    $caseWhenSql .= " ELSE {$updateColumn} END";
                }
            }
            $updateSql[] = $caseWhenSql;
        }
        $sql .= implode(',', $updateSql);

        $marks = str_repeat('?,', count($whereIdentifies) - 1).'?';
        $sql .= " WHERE {$whereIdentifyColumn} IN ({$marks})";
        $params = array_merge($params, $whereIdentifies);

        return $this->db()->executeUpdate($sql, $params);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time'
            ],
            'orderbys' => [],
            'serializes' => [
                'response' => 'json',
                'revise' => 'json'
            ],
            'conditions' => [
                'answer_record_id = :answer_record_id',
                'answer_record_id IN (:answer_record_ids)',
                'status = :status',
                'status IN (:statues)',
                'status != (:not_status)',
                'id IN (:ids)',
                'question_id = :question_id',
                'assessment_id IN (:assessment_ids)',
            ],
        ];
    }
}
