<?php

namespace Topxia\Service\Course\Dao;

interface ExerciseItemDao
{

	public function getItem($id);

	public function addItem($item);

	public function updateItem($id, $fields);

	public function deleteItem($id);

    public function deleteItemsByExerciseId($id);

}