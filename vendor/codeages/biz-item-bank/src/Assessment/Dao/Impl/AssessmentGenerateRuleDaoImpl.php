<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao\Impl;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentGenerateRuleDao;

class AssessmentGenerateRuleDaoImpl extends AdvancedDaoImpl implements AssessmentGenerateRuleDao
{
    protected $table = 'biz_assessment_generate_rule';

    public function getByAssessmentId($assessmentId)
    {
        return $this->getByFields(['assessment_id' => $assessmentId]);
    }

    public function findByAssessmentIds($assessmentIds)
    {
        return $this->findInField('assessment_id', $assessmentIds);
    }

    public function declares()
    {
        return array(
            'orderbys' => [
                'id',
                'created_time',
            ],
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'serializes' => [
                'question_setting' => 'json',
                'difficulty' => 'json'
            ],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'id NOT IN (:notInIds)',
                'assessment_id = :assessment_id',
            ],
        );
    }
}
