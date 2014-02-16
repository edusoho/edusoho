<?php
namespace Topxia\Service\Testpaper\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Testpaper\Dao\TestpaperResultDao;

class TestpaperResultDaoImpl extends BaseDao implements TestpaperResultDao
{
	protected $table = 'testpaper_result';

    public function getTestpaperResult($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findTestpaperResultsByIds(array $ids)
    {

    }

    public function findTestpaperResultByTestpaperIdAndUserIdAndActive($testpaperId, $userId)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE testId = ? AND userId = ? AND active = 1";
        return $this->getConnection()->fetchAssoc($sql, array($testpaperId, $userId));
    }

    public function findTestPaperResultsByTestIdAndStatusAndUserId($testpaperId, array $status, $userId)
    {
    	$marks = str_repeat('?,', count($status) - 1) . '?';
        array_push($status, $testpaperId, $userId);
        $sql = "SELECT * FROM {$this->table} WHERE `status` IN ({$marks}) AND `testId` = ? AND `userId` = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, $status) ? : null;
    }

    public function findTestPaperResultsByStatusAndTestIds ($ids, $status, $start, $limit)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';

        array_push($ids, $status);

        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `testId` IN ({$marks}) AND `status` = ? ORDER BY endTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, $ids) ? : array();
    }

    public function findTestPaperResultCountByStatusAndTestIds ($ids, $status)
    {
        if(empty($ids)){ 
            return null; 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';

        array_push($ids, $status);

        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `testId` IN ({$marks}) AND `status` = ?";
        return $this->getConnection()->fetchColumn($sql, $ids);
    }

    public function findTestPaperResultsByStatusAndTeacherIds ($ids, $status, $start, $limit)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';

        array_push($ids, $status);

        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `checkTeacherId` IN ({$marks}) AND `status` = ? ORDER BY endTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, $ids) ? : array();
    }

    public function findTestPaperResultCountByStatusAndTeacherIds ($ids, $status)
    {
        if(empty($ids)){ 
            return null; 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';

        array_push($ids, $status);

        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `checkTeacherId` IN ({$marks}) AND `status` = ?";
        return $this->getConnection()->fetchColumn($sql, $ids);
    }

    public function findTestPaperResultsByUserId ($id, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `userId` = ? ORDER BY beginTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($id)) ? : array();
    }

    public function findTestPaperResultsCountByUserId ($id)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `userId` = ?";
        return $this->getConnection()->fetchColumn($sql, array($id));
    }

    public function searchTestpaperResults($conditions, $sort, $start, $limit)
    {

    }

    public function searchTestpaperResultsCount($conditions)
    {

    }

    public function addTestpaperResult($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert testpaperResult error.');
        }
        return $this->getTestpaperResult($this->getConnection()->lastInsertId());
    }

    public function updateTestpaperResult($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getTestpaperResult($id);
    }

    public function updateTestpaperResultActive($testId,$userId)
    {
        $sql = "UPDATE {$this->table} SET `active` = 0 WHERE `testId` = ? AND `userId` = ? AND `active` = 1";
        return $this->getConnection()->executeQuery($sql, array($testId, $userId));
    }
}