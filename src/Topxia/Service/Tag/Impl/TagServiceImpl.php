<?php
namespace Topxia\Service\Tag\Impl;

use Topxia\Service\Tag\TagService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class TagServiceImpl extends BaseService implements TagService
{
    public function getTag($id)
    {
        return $this->getTagDao()->getTag($id);
    }

    public function getTagGroup($id)
    {
        return $this->getTagGroupDao()->getTagGroup($id);
    }

    public function getTagByName($name)
    {
        return $this->getTagDao()->getTagByName($name);
    }

    public function getTagGroupByName($name)
    {
        return $this->getTagGroupDao()->getTagGroupByName($name);
    }

    public function getTagByLikeName($name)
    {
        return $this->getTagDao()->getTagByLikeName($name);
    }

    public function findAllTagGroupsByCount($start, $limit)
    {
        return $this->getTagGroupDao()->findAllTagGroupsByCount($start, $limit);
    }

    public function getAllGroupCount()
    {
        return $this->getTagGroupDao()->findAllTagGroupsCount();
    }

    public function findAllTags()
    {
        return $this->getTagDao()->findAllTags();
    }

    public function findAllTagGroups()
    {
        return $this->getTagGroupDao()->findAllTagGroups();
    }

    public function findTagGroupsByTypes(array $types)
    {
        return $this->getTagGroupDao()->findTagGroupsByTypes($types);
    }

    public function findTagsByIds(array $ids)
    {
        return $this->getTagDao()->findTagsByIds($ids);
    }

    public function findTagsByNames(array $names)
    {
        return $this->getTagDao()->findTagsByNames($names);
    }

    public function isTagNameAvalieable($name, $exclude=null)
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

    public function isTagGroupNameAvalieable($name, $exclude=null)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $tagGroup = $this->getTagGroupByName($name);

        return $tagGroup ? false : true;
    }

    public function addTagGroup(array $tagGroup)
    {
        $tagGroup = ArrayToolkit::parts($tagGroup, array('type','name'));

        $this->filterTagGroupFields($tagGroup);
        $disabledTagGroup = $this->getTagGroupDao()->getDisabledTagGroupByName($tagGroup['name']);

        if(!empty($disabledTagGroup)) {
            $fields = array (
                'type' => $tagGroup['type'],
                'name' => $disabledTagGroup['name'],
                'disabled' => '0',
                'createdTime' => time()
            );
            $tagGroup = $this->getTagGroupDao()->updateTagGroup($disabledTagGroup['id'], $fields);
        } else {
            $tagGroup['createdTime'] = time();
            $tagGroup = $this->getTagGroupDao()->addTagGroup($tagGroup);
        }

        $this->getLogService()->info('tagGroup', 'create', "添加标签组{$tagGroup['name']}(#{$tagGroup['id']})");

        return $tagGroup;
    }

    public function addTag($tag,$groupId)
    {
        $disabledTag = $this->getTagDao()->getDisabledTagByName($tag);
        if(!empty($disabledTag)){
            $fields = array (
                'name' => $disabledTag['name'],
                'groupId' => $groupId,
                'disabled' => '0',
                'createdTime' => time()
            );
            $tag = $this->getTagDao()->updateTag($disabledTag['id'], $fields);
        } else {
             $fields = array (
                'name' => $tag,
                'groupId' => $groupId,
                'createdTime' => time()
            );
            $tag = $this->getTagDao()->addTag($fields);
        }
        return $tag;
    }

    public function findTagsByTagGroupIds($tagGroupIds)
    {
        return $this->getTagDao()->findTagsByTagGroupIds($tagGroupIds);
    }

    public function updateTagGroup($id, array $fields)
    {
        $tagGroup = $this->getTagGroup($id);
        if (empty($tagGroup)) {
            throw $this->createServiceException("标签组(#{$id})不存在，更新失败！");
        }

        $disabledTagGroup = $this->getTagGroupDao()->getDisabledTagGroupByName($fields['name']);

        if(!empty($disabledTagGroup)) {
            $fields = array (
                'type' => $tagGroup['type'],
                'name' => $fields['name'],
                'disabled' => '0',
                'createdTime' => $tagGroup['createdTime']
            );
            $this->getTagGroupDao()->updateTagGroupToDisabled($tagGroup['id']);
            $this->getTagDao()->updateTagsByGroupId($tagGroup['id'],$disabledTagGroup['id']);
            $tagGroup = $this->getTagGroupDao()->updateTagGroup($disabledTagGroup['id'], $fields);
        } else {
            $fields = ArrayToolkit::parts($fields, array('name','type'));
            $tagGroup = $this->getTagGroupDao()->updateTagGroup($id, $fields);
        }
        $this->getLogService()->info('tagGroup', 'update', "编辑标签组{$fields['name']}(#{$id})");

        return $tagGroup;
    }

    public function updateTag($id, array $fields)
    {
        $tag = $this->getTag($id);
        if (empty($tag)) {
            throw $this->createServiceException("标签(#{$id})不存在，更新失败！");
        }

        $disabledTag = $this->getTagDao()->getDisabledTagByName($fields['name']);

        if(!empty($disabledTag)){
            $fields = array (
                'name' => $disabledTag['name'],
                'groupId' => $tag['groupId'],
                'disabled' => '0',
                'createdTime' => $tag['createdTime']
            );
            $this->getTagDao()->updateTagToDisabled($id);
            $tag = $this->getTagDao()->updateTag($disabledTag['id'], $fields);
        } else {
            $fields = ArrayToolkit::parts($fields, array('name'));
            $tag = $this->getTagDao()->updateTag($id, $fields);
        }

        $this->getLogService()->info('tag', 'update', "编辑标签{$fields['name']}(#{$id})");

        return $tag;
    }

    public function deleteTagGroup($id)
    {
        $this->getTagGroupDao()->updateTagGroupToDisabled($id);
        $this->getTagDao()->updateTagToDisabledByGroupId($id);

        $this->getLogService()->info('tagGroup', 'delete', "删除标签组#{$id}");
    }

    public function deleteTag($id)
    {
        $this->getTagDao()->updateTagToDisabled($id);
        $this->getLogService()->info('tag', 'delete', "删除标签#{$id}");
    }

    private function filterTagGroupFields(&$tagGroup, $relatedTag = null)
    {
        if (empty($tagGroup['name'])) {
            throw $this->createServiceException('标签组不能为空，添加失败！');
        }

        $tagGroup['name'] = (string) $tagGroup['name'];

        $exclude = $relatedTag ? $relatedTag['name'] : null;
        if (!$this->isTagGroupNameAvalieable($tagGroup['name'], $exclude)) {
            throw $this->createServiceException('该标签组已存在，添加失败！');
        }

        return $tagGroup;
    }

    private function getTagDao()
    {
        return $this->createDao('Tag.TagDao');
    }

    private function getTagGroupDao()
    {
        return $this->createDao('Tag.TagGroupDao');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}  