<?php
namespace Topxia\Service\Taxonomy;

interface TagGroupService
{
    public function get($id);

    public function create($fields);

    public function delete($id);

    public function update($id, $fields);
}
