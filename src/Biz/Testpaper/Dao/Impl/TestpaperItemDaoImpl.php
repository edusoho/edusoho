<?php

namespace Biz\Testpaper\Dao\Impl;

use Biz\Testpaper\Dao\TestpaperItemDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class TestpaperItemDaoImpl extends AdvancedDaoImpl implements TestpaperItemDao
{
    protected $table = 'testpaper_item_v8';

    public function getItemsCountByTestId($testId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE testId = ? ";

        return $this->db()->fetchColumn($sql, array($testId));
    }

    public function getItemsCountByParams(array $conditions, $groupBy = '')
    {
        $sql = 'count(id) as num, sum(score) as score';
        if (!empty($groupBy)) {
            $sql .= ",{$groupBy}";
        }

        $builder = $this->createQueryBuilder($conditions)
            ->select($sql);

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

    public function findItemsByTestId($testpaperId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testId = ? AND type = ? ORDER BY seq ASC,id ASC";

        return $this->db()->fetchAll($sql, array($testpaperId, $type));
    }

    public function findItemsByTestIds($testpaperIds)
    {
        return $this->findInField('testId', $testpaperIds);
    }

    public function findTestpaperItemsByCopyIdAndLockedTestIds($copyId, $testIds)
    {
        if (empty($testIds)) {
            return array();
        }

        $params = array_merge(array($copyId), $testIds);
        $marks = str_repeat('?,', count($testIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE copyId = ?  AND testId IN ({$marks})";

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
        $sql = "DELETE FROM {$this->table} WHERE id IN ({$marks});";

        return $this->db()->executeUpdate($sql, $ids);
    }

    public function updateItemsMissScoreByPaperIds(array $ids, $missScore)
    {
        if (empty($ids)) {
            return array();
        }

        $params = array_merge(array($missScore), $ids);
        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "UPDATE {$this->table} SET missScore = ? WHERE testId IN ({$marks})";

        return $this->db()->executeUpdate($sql, $params);
    }

    public function declares()
    {
        $declares['conditions'] = array(
            'testId = :testId',
            'questionType IN ( :questionTypes )',
            'parentId = :parentIdDefault',
            'parentId > :parentId',
            'type = :type',
        );

        $declares['orderbys'] = array(
            'id',
        );

        return $declares;
    }
}
