<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\QuestionCategoryDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class QuestionCategoryDaoImpl extends BaseDao implements QuestionCategoryDao
{
    protected $table = 'question_category';

    public function getQuestionCategory($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addQuestionCategory($category)
    {
        $category = $this->getConnection()->insert($this->table, $category);
        if ($category <= 0) {
            throw $this->createDaoException('Insert category error.');
        }
        return $this->getQuestionCategory($this->getConnection()->lastInsertId());
    }

    public function deleteQuestionCategory($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    } 

    private function _createSearchQueryBuilder($conditions)
    {
    	if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'question_category')
            ->andWhere('questionType = :questionType')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetType = :targetType');
    }

    public function searchQuestionCategorysCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
             ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchQuestionCategorys($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('createdTime', 'DESC');

        return $builder->execute()->fetchAll() ? : array();
    }

    public function findQuestionCategorysByIds(array $ids)
    {
        if(empty($ids)){ 
        	return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function deleteQuestionCategorysByIds(array $ids)
    {
        if(empty($ids)){ 
        	return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }

}