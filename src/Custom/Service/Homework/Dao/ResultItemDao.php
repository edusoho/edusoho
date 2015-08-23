<?php
namespace Custom\Service\Homework\Dao;

interface ResultItemDao
{
    /**
     * 根据获取答题明细列表.
     * @param $resultId
     * @return 答题列表.
     */
    public function findItemsByResultId($resultId);

    /**
     * 根据userId查找homework_item_result
     * @param $userId
     * @return mixed
     */
    public function findItemResultsbyUserId($userId);
}