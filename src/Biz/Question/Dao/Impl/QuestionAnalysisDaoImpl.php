<?php

namespace Biz\Question\Dao\Impl;

use Biz\Question\Dao\QuestionAnalysisDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuestionAnalysisDaoImpl extends AdvancedDaoImpl implements QuestionAnalysisDao
{
    protected $table = 'question_analysis';

    public function getAnalysisItem($targetId, $targetType, $questionId, $choiceIndex)
    {
        $sql = "SELECT * FROM {$this->table} WHERE targetId = ? AND targetType = ? AND questionId = ? AND choiceIndex = ?";
        return $this->db()->fetchAssoc($sql, array($targetId, $targetType, $questionId, $choiceIndex));
    }

    public function findByTargetIdAndTargetType($targetId, $targetType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE targetId = ? AND targetType = ?";
        return $this->db()->fetchAll($sql, array($targetId, $targetType));
    }

    public function findByTargetIdAndTargetTypeAndQuestionId($targetId, $targetType, $questionId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE targetId = ? AND targetType = ? AND questionId = ?";
        return $this->db()->fetchAll($sql, array($targetId, $targetType, $questionId));
    }

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
                'activityId = :activityId'
            )
        );

        return $declares;
    }
}
