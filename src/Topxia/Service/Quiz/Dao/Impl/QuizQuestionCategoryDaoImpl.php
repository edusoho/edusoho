<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\QuizQuestionCategoryDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class QuizQuestionCategoryDaoImpl extends BaseDao implements QuizQuestionCategoryDao
{
    protected $table = 'quiz_question_category';

    public function getCategory($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addCategory($category)
    {
        $category = $this->getConnection()->insert($this->table, $category);
        if ($category <= 0) {
            throw $this->createDaoException('Insert category error.');
        }
        return $this->getCategory($this->getConnection()->lastInsertId());
    }

    public function updateCategory($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getCategory($id);
    }

    public function deleteCategory($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }  

    public function searchCategoryCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
             ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchCategory($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function findCategorysByCourseIds(array $ids)
    {
        if(empty($ids)){ 
        	return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE targetType='course' and targetId IN ({$marks})order by seq asc;";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function getCategorysCountByCourseId($courseId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE targetType='course' and targetId = ? ";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function deleteCategorysByIds(array $ids)
    {
        if(empty($ids)){ 
        	return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }

    private function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'questions')
            ->andWhere('userId = :userId');

         if (!empty($conditions['target'])) {
            $target = array();
            foreach ($conditions['target'] as $targetType => $targetIds) {
                foreach ($targetIds as $key => $targetId) {
                    $targetIds[] = (int) $targetId;
                }
                
                $target[] = " targetType ='".$targetType."' and targetId in (".join(' , ', $targetIds).")";
            }
            if (!empty($target)) {
                $target = join(' or ', $target);
                $builder->andStaticWhere(" ($target) ");
            }
        }

        return $builder;
    }

}