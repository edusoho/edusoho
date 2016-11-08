<?php
namespace Topxia\Service\Taxonomy\Dao;

interface TagGroupDao
{
    public function get($id);

    public function create($fields);

    public function delete($id);

    public function update($id, $fields);

    public function search($conditions, $order, $start, $limit);
}
