<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao\Impl;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AssessmentSectionItemDaoImpl extends AdvancedDaoImpl implements AssessmentSectionItemDao
{
    protected $table = 'biz_assessment_section_item';

    public function getByAssessmentIdAndItemId($assessmentId, $itemId)
    {
        return $this->getByFields(['assessment_id'=>$assessmentId, 'item_id'=>$itemId]);
    }

    public function findByAssessmentId($assessmentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE assessment_id = ? order by seq ASC";

        return $this->db()->fetchAll($sql, [$assessmentId]);
    }

    public function deleteByAssessmentId($assessmentId)
    {
        $sql = "DELETE FROM {$this->table} WHERE assessment_id = ?";

        return $this->db()->executeUpdate($sql, [$assessmentId]);
    }

    public function declares()
    {
        return array(
            'orderbys' => [
                'id',
                'created_time',
            ],
            'serializes' => [
                'score_rule' => 'json',
                'answer_mode' => 'json',
                'question_scores' => 'json',
            ],
            'orderbys' => [
                'id',
                'created_time',
            ],
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'conditions' => [
                'id = :id',
                'assessment_id = :assessment_id',
                'assessment_id in (:assessmentIds)',
            ],
        );
    }
}
