<?php

namespace Biz\Question\Dao\Impl;

use Biz\Question\Dao\QuestionFavoriteDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class QuestionFavoriteDaoImpl extends GeneralDaoImpl implements QuestionFavoriteDao
{
    protected $table = 'question_favorite';

    public function findUserFavoriteQuestions($userId)
    {
        return $this->findInField('userId', array($userId));
    }

    public function deleteFavoriteByQuestionId($questionId)
    {
        return $this->db()->delete($this->table, array('questionId' => $questionId));
    }

    public function declares()
    {
        $declares['orderbys'] = array(
            'createdTime',
        );

        $declares['conditions'] = array(
            'targetType = :targetType',
            'targetId = :targetId',
            'userId = :userId',
            'questionId IN ( :questionIds )',
        );

        return $declares;
    }
}
