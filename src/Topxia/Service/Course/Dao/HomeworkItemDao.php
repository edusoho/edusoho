<?php

namespace Topxia\Service\Course\Dao;

interface HomeworkItemDao
{

    public function getItem($id);

    public function addItem($items);

    public function updateItem($id, $fields);

    public function deleteItem($id);

    public function deleteItemsByParentId($id);

    public function deleteItemsByTestpaperId($id);

    public function findItemByIds(array $ids);

    public function findItemsByHomeworkId($homeworkId);

    public function getItemsCountByHomeworkId($homeworkId);

    public function getItemsCountByHomeworkIdAndQuestionType($homeworkId, $questionType);

    public function deleteItemByIds(array $ids);

}