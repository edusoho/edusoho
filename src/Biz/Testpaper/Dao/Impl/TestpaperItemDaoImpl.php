<?php

namespace Biz\Testpaper\Dao\Impl;

use Biz\Testpaper\Dao\TestpaperItemDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TestpaperItemDaoImpl extends GeneralDaoImpl implements TestpaperItemDao
{
    protected $table = 'testpaper_item';

    public function getItemsCountByTestId($testId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE testId = ? ";
        return $this->db()->fetchColumn($sql, array($testId));
    }

    public function getItemsCountByParams(array $conditions, $groupBy = '')
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('count(id) as num, sum(score) as score,questionType');

        if (!empty($groupBy)) {
            $builder->addGroupBy($groupBy);
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    public function getItemsCountByTestIdAndParentId($testId, $parentId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `testId` = ? AND `parentId` = ?";
        return $this->db()->fetchColumn($sql, array($testId, $parentId));
    }

    public function getItemsCountByTestIdAndQuestionType($testId, $questionType)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `testId` = ? AND `questionType` = ? ";
        return $this->db()->fetchColumn($sql, array($testId, $questionType));
    }

    public function findItemsByIds(array $ids)
    {
        return $this->findInField('id', array($ids));
    }

    public function findItemsByTestId($testpaperId)
    {
        return $this->findInField('testId', array($testpaperId));
    }

    public function findTestpaperItemsByPIdAndLockedTestIds($pId, $testIds)
    {
        if (empty($testIds)) {
            return array();
        }

        $params = array_merge(array($pId), $testIds);
        $marks  = str_repeat('?,', count($testIds) - 1).'?';
        $sql    = "SELECT * FROM {$this->table} WHERE pId = ?  AND testId IN ({$marks})";
        return $this->db()->fetchAll($sql, $params);
    }

    public function deleteItemsByParentId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE parentId = ?";
        return $this->db()->executeUpdate($sql, array($id));
    }

    public function deleteItemsByTestpaperId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE testId = ? ";
        return $this->db()->executeUpdate($sql, array($id));
    }

    public function deleteItemByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->db()->executeUpdate($sql, $ids);
    }

    public function updateItemsMissScoreByPaperIds(array $ids, $missScore)
    {
        if (empty($ids)) {
            return array();
        }

        $params = array_merge(array($missScore), $ids);
        $marks  = str_repeat('?,', count($ids) - 1).'?';
        $sql    = "UPDATE {$this->table} SET missScore = ? WHERE testId IN ({$marks})";
        return $this->db()->executeUpdate($sql, $params);
    }

    public function declares()
    {
        $declares['conditions'] = array(
            'testId = :testId',
            'questionType IN ( :questionTypes )',
            'parentId = :parentIdDefault',
            'parentId > :parentId'
        );

        $declares['orderbys'] = array(
            'id'
        );

        return $declares;
    }
}
