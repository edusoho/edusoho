<?php
namespace Biz\Testpaper\Dao\Impl;

use Biz\Testpaper\Dao\TestpaperResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TestpaperResultDaoImpl extends GeneralDaoImpl implements TestpaperResultDao
{
    protected $table = 'testpaper_result';

    public function getUserUnfinishResult($testId, $courseId, $lessonId, $type, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testId = ? AND courseId = ? AND lessonId = ? AND type = ? AND userId = ? AND status != 'finished' ORDER BY id DESC ";
        return $this->db()->fetchAssoc($sql, array($testId, $courseId, $lessonId, $type, $userId));
    }

    public function getUserLatelyResultByTestId($userId, $testId, $courseId, $lessonId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND testId = ? AND courseId = ? AND lessonId = ? AND type = ? ORDER BY endTime DESC ";
        return $this->db()->fetchAssoc($sql, array($userId, $testId, $courseId, $lessonId, $type));
    }

    public function findPaperResultsStatusNumGroupByStatus($testId)
    {
        $sql = "SELECT status,COUNT(id) AS num FROM {$this->table} WHERE testId=? GROUP BY status";
        return $this->db()->fetchAll($sql, array($testId)) ?: array();
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
