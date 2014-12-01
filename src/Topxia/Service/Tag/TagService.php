<?php
namespace Topxia\Service\Tag;

interface TagService
{
    public function getTag($id);

    public function getTagGroup($id);

    public function getTagByName($name);

    public function getTagGroupByName($name);

    public function getTagByLikeName($name);

    public function findAllTagGroupsByCount($start, $limit);

    public function getAllGroupCount();

    public function findAllTags();

    public function findAllTagGroups();

    public function findTagsByIds(array $ids);

    public function findTagsByNames(array $names);

    public function isTagNameAvalieable($name, $exclude=null);

    public function isTagGroupNameAvalieable($name, $exclude=null);

    public function addTagGroup(array $tagGroup);

    public function addTag($tag,$groupId);

    public function findTagsByTagGroupIds($tagGroupIds);

    public function updateTagGroup($id, array $fields);

    public function updateTag($id, array $fields);

    public function deleteTagGroup($id);

    public function deleteTag($id);

    public function findTagGroupsByTypes(array $types);
}

