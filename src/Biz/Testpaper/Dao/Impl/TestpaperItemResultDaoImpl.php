<?php

namespace Biz\Testpaper\Dao\Impl;

use Biz\Testpaper\Dao\TestpaperItemResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TestpaperItemResultDaoImpl extends GeneralDaoImpl implements TestpaperItemResultDao
{
    protected $table = 'testpaper_item_result_v8';

    public function findItemResultsByResultId($resultId, $type)
    {
        return $this->findByFields(array('resultId' => $resultId, 'type' => $type));
    }

    public function addItemAnswers($testPaperResultId, $answers, $testPaperId, $userId)
    {
        if (empty($answers)) {
            return array();
        }

        $answers = array_map(function ($answer) {
            return json_encode($answer);
        }, $answers);

        $mark = '('.str_repeat('?,', 4).'? )';
        $marks = str_repeat($mark.',', count($answers) - 1).$mark;
        $answersForSQL = array();

        foreach ($answers as $key => $value) {
            array_push($answersForSQL, (int) $testPaperId, (int) $testPaperResultId, (int) $userId, (int) $key, $value);
        }

        $sql = "INSERT INTO {$this->table} (`testId`, `resultId`, `userId`, `questionId`, `answer`) VALUES {$marks};";

        return $this->db()->executeUpdate($sql, $answersForSQL);
    }

    //要不要给这三个字段加上索引呢
    public function updateItemAnswers($testPaperResultId, $answers)
    {
        if (empty($answers)) {
            return array();
        }

        $answers = array_map(function ($answer) {
            return json_encode($answer);
        }, $answers);

        $sql = '';
        $answersForSQL = array();

        $this->db()->beginTransaction();
        try {
            foreach ($answers as $key => $value) {
                $sql = "UPDATE {$this->table} set `answer` = ? WHERE `questionId` = ? AND `resultId` = ?;";
                $answersForSQL = array($value, (int) $key, (int) $testPaperResultId);
                $this->db()->executeQuery($sql, $answersForSQL);
            }

            $this->db()->commit();
        } catch (\Exception $e) {
            $this->db()->rollback();
            throw $e;
        }
    }

    public function updateItemResults($testPaperResultId, $answers)
    {
        if (empty($answers)) {
            return array();
        }

        $sql = '';
        $answersForSQL = array();

        $this->db()->beginTransaction();
        try {
            foreach ($answers as $key => $value) {
                $sql = "UPDATE {$this->table} set `status` = ?, `score` = ? WHERE `questionId` = ? AND `resultId` = ?;";
                $answersForSQL = array($value['status'], $value['score'], (int) $key, (int) $testPaperResultId);
                $this->db()->executeQuery($sql, $answersForSQL);
            }

            $this->db()->commit();
        } catch (\Exception $e) {
            $this->db()->rollback();
            throw $e;
        }
    }

    public function updateItemEssays($testPaperResultId, $answers)
    {
        if (empty($answers)) {
            return array();
        }

        $sql = '';
        $answersForSQL = array();

        $this->db()->beginTransaction();
        try {
            foreach ($answers as $key => $value) {
                $sql = "UPDATE {$this->table} set `score` = ?, `teacherSay` = ?, `status` = ? WHERE `questionId` = ? AND `resultId` = ?;";
                $answersForSQL = array($value['score'], $value['teacherSay'], $value['status'], (int) $key, (int) $testPaperResultId);
                $this->db()->executeQuery($sql, $answersForSQL);
            }

            $this->db()->commit();
        } catch (\Exception $e) {
            $this->db()->rollback();
            throw $e;
        }
    }

    public function findTestResultsByItemIdAndTestId($questionIds, $testPaperResultId)
    {
        if (empty($questionIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($questionIds) - 1).'?';

        $questionIds[] = $testPaperResultId;

        $sql = "SELECT * FROM {$this->table} WHERE questionId IN ({$marks}) AND resultId = ?";

        return $this->db()->fetchAll($sql, $questionIds) ?: array();
    }

    public function countRightItemByTestPaperResultId($testPaperResultId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE resultId = ? AND status = 'right' ";

        return $this->db()->fetchColumn($sql, array($testPaperResultId));
    }

    public function findWrongResultByUserId($id, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `userId` = ? AND `status` in ('wrong')";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($id)) ?: array();
    }

    public function countWrongResultByUserId($id)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `userId` = ? AND `status` in ('wrong')";

        return $this->db()->fetchColumn($sql, array($id));
    }

    public function deleteTestpaperItemResultByTestpaperId($testpaperId)
    {
        $sql = "DELETE FROM {$this->table} WHERE testId = ?";

        return $this->db()->executeUpdate($sql, array($testpaperId));
    }

    public function declares()
    {
        $declares['serializes'] = array(
            'answer' => 'json',
        );

        return $declares;
    }
}
