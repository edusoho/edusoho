<?php

namespace Topxia\Service\File\Dao;


interface FileUsedDao
{
    public function get($id);

    public function create($fileUsed);

    public function update($id, $filed);

    public function delete($id);

    public function search($conditions, $order, $start, $limit);

    public function count($conditions);
}