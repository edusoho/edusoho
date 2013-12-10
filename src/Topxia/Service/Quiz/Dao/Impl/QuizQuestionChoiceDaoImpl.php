<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\QuizQuestionChoiceDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class QuizQuestionChoiceDaoImpl extends BaseDao implements QuizQuestionChoiceDao
{
    protected $table = 'quiz_question_choice';

    public function getChoice($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getChoicesByQuesitonId($questionId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE questionId = ? ";
        return $this->getConnection()->fetchAll($sql, array($questionId));
    }

    public function addChoice($choice)
    {
        $choice = $this->getConnection()->insert($this->table, $choice);
        if ($choice <= 0) {
            throw $this->createDaoException('Insert choice error.');
        }
        return $this->getChoice($this->getConnection()->lastInsertId());
    }

    public function updateChoice($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getChoice($id);
    }

    public function deleteChoice($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    } 

    public function findChoicesByQuestionIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE questionId IN ({$marks})";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function deleteChoicesByQuestionIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE questionId IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }


}