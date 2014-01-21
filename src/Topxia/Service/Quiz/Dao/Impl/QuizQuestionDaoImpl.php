<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\QuizQuestionDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class QuizQuestionDaoImpl extends BaseDao implements QuizQuestionDao
{
    protected $table = 'quiz_question';

    public function getQuestion($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addQuestion($questions)
    {
        $questions = $this->getConnection()->insert($this->table, $questions);
        if ($questions <= 0) {
            throw $this->createDaoException('Insert questions error.');
        }
        return $this->getQuestion($this->getConnection()->lastInsertId());
    }

    public function updateQuestion($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getQuestion($id);
    }

    public function deleteQuestion($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    } 

    public function deleteQuestionsByParentId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function searchQuestionCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
             ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchQuestion($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function findQuestionsByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids) ? : array();
    }


    public function findQuestionsPaginatorByIds(array $ids, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "SELECT * FROM {$this->table} WHERE `id` IN ({$marks}) LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, $ids) ? : array();
    }

    public function findQuestionsPaginatorCountByUserId (array $ids)
    {
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `id` IN ({$marks})";
        return $this->getConnection()->fetchColumn($sql, $ids);
    }

    public function findQuestionsByTypeAndTypeIds($type, $ids)
    {
        if(empty($ids)||empty($type)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE targetId IN ({$marks}) and targetType = ?;";
        $ids[] = $type;
        return $this->getConnection()->fetchAll($sql, $ids) ? : array();
    }

    public function findQuestionsCountByTypeAndTypeIds($type, $ids)
    {
        if(empty($ids)||empty($type)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT count(*) FROM {$this->table} WHERE targetType = ? and targetId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $type + $ids) ? : array();
    }

    public function findQuestionsByParentIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE parentId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids) ? : array();
    }

    public function deleteQuestionByIds(array $ids)
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
        if (isset($conditions['stem'])) {
            $conditions['stem'] = "%{$conditions['stem']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'questions')
            ->andWhere('type = :type')
            ->andWhere('parentId = :parentId')
            ->andWhere('targetId = :targetId')
            ->andWhere('stem LIKE :stem')
            ->andWhere('targetType = :targetType');

        if(!empty($conditions['parentIds'])){
            if(trim(implode($conditions['parentIds'], ',')) != "")
                $builder->andStaticWhere(" parentId in (".implode($conditions['parentIds'], ',').") ");
        }

        if(!empty($conditions['notId'])){
            if(trim(implode($conditions['notId'], ',')) != "")
                $builder->andStaticWhere(" id not in (".implode($conditions['notId'], ',').") ");
        }

        if (!empty($conditions['target'])) {
            $target = array();
            foreach ($conditions['target'] as $targetType => $targetIds) {
                foreach ($targetIds as  $targetId) {
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