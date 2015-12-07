<?php

namespace Mooc\Service\Testpaper\Dao\Impl;

use Topxia\Service\Testpaper\Dao\Impl\TestpaperItemResultDaoImpl as BaseTestpaperItemResultDaoImpl;

class TestpaperItemResultDaoImpl extends BaseTestpaperItemResultDaoImpl
{
    public function findTestpaperItemResultsByTestIdAndQuestionIdAndStatus($questionId, $testpaperId, $status)
    {
        $sql = "SELECT * FROM {$this->table} WHERE questionId = ?  AND testId = ?";

        if ($status) {
            $sql .= "AND status = 'right' ";
        } else {
            $sql .= "AND status <> 'right' ";
        }

        return $this->getConnection()->fetchAll($sql, array($questionId, $testpaperId));
    }
}
