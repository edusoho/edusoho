<?php

namespace Biz\QuestionTag\Service;

interface QuestionTagService
{
    public function getTagGroupByName($name);

    public function createTagGroup($name);

    public function updateTagGroup($id, $params);

    public function deleteTagGroup($id);

    public function searchTagGroups($conditions, $columns = []);

    public function sortTagGroups($ids);

    public function getTagByGroupIdAndName($groupId, $name);

    public function createTag($params);

    public function updateTag($id, $params);

    public function deleteTag($id);

    public function searchTags($conditions, $columns = []);

    public function sortTags($groupId, $ids);

    public function tagQuestions($itemIds, $tagIds);

    public function findTagRelationsByTagIds($tagIds);

    public function findTagRelationsByItemIds($itemIds);
}
