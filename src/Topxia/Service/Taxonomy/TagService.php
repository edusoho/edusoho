<?php
namespace Topxia\Service\Taxonomy;

interface TagService
{

	public function addTag(array $tag);

	public function updateTag($id, array $fields);

	public function getTag($id);

	public function getTagByName($name);

    public function getAllTags($start, $limit);

    public function getAllTagsCount();

    public function getTagsByIds(array $ids);

    public function getTagsByNames(array $names);

    public function deleteTag($id);
}

