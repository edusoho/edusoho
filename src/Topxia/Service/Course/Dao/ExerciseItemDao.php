<?php

namespace Topxia\Service\Course\Dao;

interface ExerciseItemDao
{
    public function getItem($id);

    public function addItem($items);

    public function deleteItem($id);

    public function deleteItemByexerciseId($exerciseId);

    public function findItemsByExerciseId($exerciseId);
}