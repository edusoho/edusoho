<?php
namespace Custom\Service\Homework\Dao;

interface HomeworkItemResultDao
{
    /**
     * 根据userIdeas查找homework_item_result
     * @param $userId
     * @return mixed
     */
    public function findItemResultsbyUserId($userId);

}