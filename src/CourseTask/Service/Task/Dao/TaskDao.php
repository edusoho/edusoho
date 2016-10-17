<?php

namespace CourseTask\Service\Task\Dao;

interface TaskDao
{
    public function get($id);

    public function add($activity);

    public function update($id, $fields);

    public function delete($id);
}
