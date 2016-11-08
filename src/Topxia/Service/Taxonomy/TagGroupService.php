<?php
namespace Topxia\Service\Taxonomy;

interface TagGroupService
{
    public function get($id);

    public function search($conditions, $order, $start, $limit);

    public function create($fields);

    public function delete($id);

    public function update($id, $fields);
}
