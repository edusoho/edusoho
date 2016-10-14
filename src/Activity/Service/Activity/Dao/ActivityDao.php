<?php

namespace Activity\Service\Activity\Dao;

interface ActivityDao
{
    public function get($id);

    public function add($activity);

    public function update($id, $fields);

    public function delete($id);
}
