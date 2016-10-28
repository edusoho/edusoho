<?php
namespace Biz\Testpaper\Dao\Impl;

use Biz\Testpaper\Dao\TestpaperResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TestpaperResultDaoImpl extends GeneralDaoImpl implements TestpaperResultDao
{
    protected $table = 'testpaper_result';

    public function getUserDoingResult($testId, $courseId, $lessonId, $type, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testId = ? AND courseId = ? AND lessonId = ? AND type = ? AND userId = ? AND status = 'doing' ORDER BY id DESC ";
        return $this->db()->fetchAssoc($sql, array($testId, $courseId, $lessonId, $type, $userId));
    }

    public function findTestpaperResultByTestpaperIdAndUserIdAndActive($testpaperId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testId = ? AND userId = ? AND active = 1 ORDER BY id DESC ";
        return $this->db()->fetchAssoc($sql, array($testpaperId, $userId));
    }

    public function findTestPaperResultsByTestIdAndStatusAndUserId($testpaperId, array $status, $userId)
    {
        $marks = str_repeat('?,', count($status) - 1).'?';
        array_push($status, $testpaperId, $userId);
        $sql = "SELECT * FROM {$this->table} WHERE `status` IN ({$marks}) AND `testId` = ? AND `userId` = ? ORDER BY id DESC LIMIT 1";
        return $this->db()->fetchAssoc($sql, $status) ?: null;
    }

    public function findTestPaperResultsByStatusAndTestIds($ids, $status, $start, $limit)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        array_push($ids, $status);

        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `testId` IN ({$marks}) AND `status` = ? ORDER BY endTime DESC LIMIT {$start}, {$limit}";
        return $this->db()->fetchAll($sql, $ids) ?: array();
    }

    public function findTestPaperResultCountByStatusAndTestIds($ids, $status)
    {
        if (empty($ids)) {
            return null;
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        array_push($ids, $status);

        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `testId` IN ({$marks}) AND `status` = ?";
        return $this->db()->fetchColumn($sql, $ids);
    }

    public function findTestPaperResultsByStatusAndTeacherIds($ids, $status, $start, $limit)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        array_push($ids, $status);

        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `checkTeacherId` IN ({$marks}) AND `status` = ? ORDER BY endTime DESC LIMIT {$start}, {$limit}";
        return $this->db()->fetchAll($sql, $ids) ?: array();
    }

    public function findTestPaperResultCountByStatusAndTeacherIds($ids, $status)
    {
        if (empty($ids)) {
            return null;
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        array_push($ids, $status);

        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `checkTeacherId` IN ({$marks}) AND `status` = ?";
        return $this->db()->fetchColumn($sql, $ids);
    }

    public function findTestPaperResultsByUserId($id, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `userId` = ? ORDER BY beginTime DESC LIMIT {$start}, {$limit}";
        return $this->db()->fetchAll($sql, array($id)) ?: array();
    }

    public function findTestPaperResultsCountByUserId($id)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `userId` = ?";
        return $this->db()->fetchColumn($sql, array($id));
    }

    public function updateTestpaperResultActive($testId, $userId)
    {
        $sql = "UPDATE {$this->table} SET `active` = 0 WHERE `testId` = ? AND `userId` = ? AND `active` = 1";
        return $this->db()->executeQuery($sql, array($testId, $userId));
    }

    public function updateTestResultsByTarget($target, $fields)
    {
        return $this->db()->update($this->table, $fields, array('target' => $target));
    }

    public function deleteTestpaperResultByTestpaperId($testpaperId)
    {
        $sql = "DELETE FROM {$this->table} WHERE testId = ?";
        return $this->db()->executeUpdate($sql, array($testpaperId));
    }

    public function deleteTestpaperResultByTestpaperIdAndStatus($testpaperId, $status)
    {
        $sql = "DELETE FROM {$this->table} WHERE `testId` = ? AND `status` = ?";
        return $this->db()->executeUpdate($sql, array($testpaperId, $status));
    }

    public function searchTestpapersScore($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('sum(score)');

        return $builder->execute()->fetchColumn(0);
    }

    public function declares()
    {
        $declares['orderbys'] = array(
            'createdTime',
            'endTime',
            'checkedTime'
        );

        $declares['conditions'] = array(
            'id = :id',
            'checkTeacherId = :checkTeacherId',
            'paperName = :paperName',
            'testId = :testId',
            'testId IN ( :testIds )',
            'userId = :userId',
            'score = :score',
            'objectiveScore = :objectiveScore',
            'subjectiveScore = :subjectiveScore',
            'rightItemCount = :rightItemCount',
            'status = :status'
        );

        return $declares;
    }
}
