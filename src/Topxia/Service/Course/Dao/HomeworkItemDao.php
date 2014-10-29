<?php

namespace Topxia\Service\Course\Dao;

interface HomeworkItemDao
{
    public function getItem($id);

    public function addItem($items);

    public function deleteItem($id);

    public function findItemsByHomeworkId($homeworkId);
}