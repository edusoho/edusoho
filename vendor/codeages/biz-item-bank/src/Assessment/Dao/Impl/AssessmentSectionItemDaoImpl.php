<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao\Impl;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AssessmentSectionItemDaoImpl extends AdvancedDaoImpl implements AssessmentSectionItemDao
{
    protected $table = 'biz_assessment_section_item';

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
                'created_time',
            ],
            'serializes' => [
                'score_rule' => 'json',
                'answer_mode' => 'json',
                'question_scores' => 'json',
            ],
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'conditions' => [
                'id = :id',
            ],
        );
    }
}
