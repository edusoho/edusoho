<?php
namespace Topxia\Service\Taxonomy\Dao;

interface TagGroupDao
{
    public function get($id);

    public function findTagGroups();

    public function create($fields);

    public function delete($id);

    public function update($id, $fields);
}
