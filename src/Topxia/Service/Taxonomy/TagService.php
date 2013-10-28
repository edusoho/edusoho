<?php
namespace Topxia\Service\Taxonomy;

interface TagService
{
	public function getTag($id);

    public function getTagByName($name);

	public function getTagByLikeName($name);

    public function findAllTags($start, $limit);

    public function getAllTagCount();

    public function findTagsByIds(array $ids);

    public function findTagsByNames(array $names);

    public function isTagNameAvalieable($name, $exclude=null);

	public function addTag(array $tag);

	public function updateTag($id, array $fields);

    public function deleteTag($id);
}

