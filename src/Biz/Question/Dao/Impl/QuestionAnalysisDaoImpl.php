<?php

namespace Biz\Question\Dao\Impl;

use Biz\Question\Dao\QuestionAnalysisDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuestionAnalysisDaoImpl extends AdvancedDaoImpl implements QuestionAnalysisDao
{
    protected $table = 'question_analysis';

    public function declares()
    {
        $declares = array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime', 'updateTime'),
            'conditions' => array(
                'targetId = :targetId',
                'targetType = :targetType',
                'questionId = :questionId',
                'questionId IN (:questionIds)',
                'activityId = :activityId',
            ),
        );

        return $declares;
    }
}
