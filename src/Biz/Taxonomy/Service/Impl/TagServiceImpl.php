<?php

namespace Biz\Taxonomy\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Dao\TagDao;
use Biz\Taxonomy\Dao\TagGroupDao;
use Biz\Taxonomy\Dao\TagGroupTagDao;
use Biz\Taxonomy\Dao\TagOwnerDao;
use Biz\Taxonomy\Service\TagService;
use Biz\Taxonomy\TagException;
use Topxia\Service\Common\ServiceKernel;

class TagServiceImpl extends BaseService implements TagService
{
    private $allowFields
        = array(
            'name',
            'scope',
            'tagNum',
        );

    public function getTag($id)
    {
        return $this->getTagDao()->get($id);
    }

    public function getTagGroup($id)
    {
        return $this->getTagGroupDao()->get($id);
    }

    public function getTagByName($name)
    {
        return $this->getTagDao()->getByName($name);
    }

    public function getTagOwnerRelationByTagIdAndOwner($tagId, $owner)
    {
        return $this->getTagOwnerDao()->getTagOwnerRelationByTagIdAndOwnerTypeAndOwnerId(
            $tagId,
            $owner['ownerType'],
            $owner['ownerId']
        );
    }

    public function findTagsByLikeName($name)
    {
        return $this->getTagDao()->findByLikeName($name);
    }

    public function findAllTags($start, $limit)
    {
        return $this->getTagDao()->findAll($start, $limit);
    }

    public function getAllTagCount()
    {
        return $this->getTagDao()->getAllCount();
    }

    public function findTagGroups()
    {
        return $this->getTagGroupDao()->find();
    }

    public function findTagRelationsByTagIds($tagIds)
    {
        return $this->getTagGroupTagDao()->findTagRelationsByTagIds($tagIds);
    }

    public function findTagGroupsByTagId($tagId)
    {
        $tagRelations = $this->findTagRelationsByTagIds(array($tagId));

        $groupIds = ArrayToolkit::column($tagRelations, 'groupId');

        return $this->getTagGroupDao()->findByIds($groupIds);
    }

    public function findTagsByGroupId($groupId)
    {
        $tagRelations = $this->getTagGroupTagDao()->findTagRelationsByGroupId($groupId);

        $tagIds = ArrayToolkit::column($tagRelations, 'tagId');

        return $this->findTagsByIds($tagIds);
    }

    public function findTagsByOwner(array $owner)
    {
        $tagOwnerRelations = $this->getTagOwnerDao()->findByOwnerTypeAndOwnerId($owner['ownerType'], $owner['ownerId']);

        $tagIds = ArrayToolkit::column($tagOwnerRelations, 'tagId');

        return $this->getTagDao()->findByIds($tagIds);
    }

    public function findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, $ownerType)
    {
        return $this->getTagOwnerDao()->findByTagIdsAndOwnerType($tagIds, $ownerType);
    }

    public function searchTags($conditions, $sort, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getTagDao()->search($conditions, $sort, $start, $limit);
    }

    public function searchTagCount($conditions)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getTagDao()->count($conditions);
    }

    private function _prepareConditions($conditions)
    {
        $magic = $this->getSettingService()->get('magic');

        if (isset($magic['enable_org']) && $magic['enable_org']) {
            $user = $this->getCurrentUser();
            $conditions['orgId'] = !empty($user['org']) ? $user['org']['id'] : null;
        }

        return $conditions;
    }

    public function findTagsByIds(array $ids)
    {
        $tags = $this->getTagDao()->findByIds($ids);

        return ArrayToolkit::index($tags, 'id');
    }

    public function findTagsByNames(array $names)
    {
        return $this->getTagDao()->findByNames($names);
    }

    public function isTagNameAvailable($name, $exclude = null)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $tag = $this->getTagByName($name);

        return $tag ? false : true;
    }

    public function isTagGroupNameAvailable($name, $exclude = null)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $tag = $this->getTagGroupDao()->getByName($name);

        return $tag ? false : true;
    }

    public function addTag(array $tag)
    {
        $tag = ArrayToolkit::parts($tag, array('name'));
        $tag = $this->filterTagFields($tag);
        $tag['createdTime'] = time();
        $tag = $this->setTagOrg($tag);
        $tag = $this->getTagDao()->create($tag);

        return $tag;
    }

    public function addTagGroup($fields)
    {
        if (empty($fields['name'])) {
            $this->createNewException(TagException::EMPTY_GROUP_NAME());
        }

        if ($this->getTagGroupDao()->getByName($fields['name'])) {
            $this->createNewException(TagException::DUPLICATE_GROUP_NAME());
        }

        $tagIds = empty($fields['tagIds']) ? array() : $fields['tagIds'];

        $fields = $this->filterTagGroupFields($fields);

        $fields['createdTime'] = time();

        $tagGroup = $this->getTagGroupDao()->create($fields);

        foreach ($tagIds as $tagId) {
            $this->getTagGroupTagDao()->create(
                array(
                    'tagId' => $tagId,
                    'groupId' => $tagGroup['id'],
                )
            );
        }

        return $tagGroup;
    }

    public function addTagOwnerRelation($fields)
    {
        return $this->getTagOwnerDao()->create($fields);
    }

    public function batchCreateTagOwner($tagOwners)
    {
        if (empty($tagOwners)) {
            return;
        }

        $this->getTagOwnerDao()->batchCreate($tagOwners);

        return true;
    }

    protected function setTagOrg($tag)
    {
        $magic = $this->getSettingService()->get('magic');

        if (empty($magic['enable_org'])) {
            return $tag;
        }

        $user = $this->getCurrentUser();
        $currentOrg = $user['org'];

        if (empty($currentOrg)) {
            return $tag;
        }

        $tag['orgId'] = $currentOrg['id'];
        $tag['orgCode'] = $currentOrg['orgCode'];

        return $tag;
    }

    public function updateTag($id, array $fields)
    {
        $tag = $this->getTag($id);

        if (empty($tag)) {
            $this->createNewException(TagException::NOTFOUND_TAG());
        }

        $fields = ArrayToolkit::parts($fields, array('name'));
        $this->filterTagFields($fields, $tag);

        return $this->getTagDao()->update($id, $fields);
    }

    public function updateTagGroup($id, $fields)
    {
        $tagGroup = $this->getTagGroupDao()->get($id);

        if (empty($tagGroup)) {
            $this->createNewException(TagException::NOTFOUND_GROUP());
        }

        if (!empty($fields['tagIds'])) {
            $this->getTagGroupTagDao()->deleteByGroupId($id);

            $tagIds = empty($fields['tagIds']) ? array() : $fields['tagIds'];

            foreach ($tagIds as $tagId) {
                $this->getTagGroupTagDao()->create(array('groupId' => $id, 'tagId' => $tagId));
            }

            $fields = $this->filterTagGroupFields($fields);

            $fields['updatedTime'] = time();

            $fields['tagNum'] = count($tagIds);
        }

        $updatedTagGroup = $this->getTagGroupDao()->update($id, $fields);

        return $updatedTagGroup;
    }

    public function deleteTag($id)
    {
        $tag = $this->getTag($id);

        $tagGroupRelations = $this->getTagGroupTagDao()->findTagRelationsByTagId($id);

        if (0 != count($tagGroupRelations)) {
            foreach ($tagGroupRelations as $tagGroupRelation) {
                $this->getTagGroupTagDao()->deleteByGroupIdAndTagId($tagGroupRelation['groupId'], $id);

                $tagGroup = $this->getTagGroup($tagGroupRelation['groupId']);

                $tagNum = $tagGroup['tagNum'] - 1;

                $this->updateTagGroup($tagGroupRelation['groupId'], array('tagNum' => $tagNum));
            }
        }

        $this->getTagDao()->delete($id);

        $this->dispatchEvent('tag.delete', array('tagId' => $id));
    }

    public function deleteTagGroup($id)
    {
        $this->getTagGroupDao()->delete($id);

        $this->getTagGroupTagDao()->deleteByGroupId($id);
    }

    public function deleteTagOwnerRelationsByOwner(array $owner)
    {
        return $this->getTagOwnerDao()->deleteByOwnerTypeAndOwnerId($owner['ownerType'], $owner['ownerId']);
    }

    public function findGroupTagIdsByOwnerTypeAndOwnerIds($ownerType, array $ids)
    {
        $tagOwnerRelations = $this->getTagOwnerDao()->findByOwnerTypeAndOwnerIds($ownerType, $ids);
        $tagIds = ArrayToolkit::group($tagOwnerRelations, 'ownerId');
        foreach ($tagIds as $key => $value) {
            $tagIds[$key] = ArrayToolkit::column($value, 'tagId');
        }

        return $tagIds;
    }

    public function findOwnerIdsByTagIdsAndOwnerType($tagIds, $ownerType)
    {
        $ownerIds = array();
        if (empty($tagIds)) {
            return $ownerIds;
        }

        $tagOwnerRelations = $this->findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, $ownerType);
        if (empty($tagOwnerRelations)) {
            return $ownerIds;
        }

        $ownerIds = ArrayToolkit::column($tagOwnerRelations, 'ownerId');
        $ownerTagCount = array_count_values($ownerIds);

        $tagIdsCount = count($tagIds);
        foreach ($ownerTagCount as $ownerId => $count) {
            if ($count != $tagIdsCount) {
                unset($ownerTagCount[$ownerId]);
            }
        }

        return array_keys($ownerTagCount);
    }

    public function findTagIdsByOwnerTypeAndOwnerIds($ownerType, array $ids)
    {
        $tagOwnerRelations = $this->getTagOwnerDao()->findByOwnerTypeAndOwnerIds($ownerType, $ids);
        $tagIds = ArrayToolkit::column($tagOwnerRelations, 'tagId');

        return $tagIds;
    }

    protected function filterTagFields(&$tag, $relatedTag = null)
    {
        if (empty($tag['name'])) {
            $this->createNewException(TagException::EMPTY_TAG_NAME());
        }

        $tag['name'] = (string) $tag['name'];

        $exclude = $relatedTag ? $relatedTag['name'] : null;

        if (!$this->isTagNameAvailable($tag['name'], $exclude)) {
            $this->createNewException(TagException::DUPLICATE_TAG_NAME());
        }

        return $tag;
    }

    protected function filterTagGroupFields($fields)
    {
        return ArrayToolkit::parts($fields, $this->allowFields);
    }

    /**
     * @return TagOwnerDao
     */
    protected function getTagOwnerDao()
    {
        return $this->createDao('Taxonomy:TagOwnerDao');
    }

    /**
     * @return TagGroupTagDao
     */
    protected function getTagGroupTagDao()
    {
        return $this->createDao('Taxonomy:TagGroupTagDao');
    }

    /**
     * @return TagDao
     */
    protected function getTagDao()
    {
        return $this->createDao('Taxonomy:TagDao');
    }

    /**
     * @return TagGroupDao
     */
    protected function getTagGroupDao()
    {
        return $this->createDao('Taxonomy:TagGroupDao');
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
