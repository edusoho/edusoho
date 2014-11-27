<?php
namespace Topxia\Service\Tag\Impl;

use Topxia\Service\Tag\TagService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class TagServiceImpl extends BaseService implements TagService
{
    public function getTag($id)
    {
        return $this->getTag2Dao()->getTag2($id);
    }

    public function getTagGroup($id)
    {
        return $this->getTag2GroupDao()->getTag2Group($id);
    }

    public function getTagByName($name)
    {
        return $this->getTag2Dao()->getTag2ByName($name);
    }

    public function getTagGroupByName($name)
    {
        return $this->getTag2GroupDao()->getTag2GroupByName($name);
    }

    public function getTag2ByLikeName($name)
    {
        return $this->getTag2Dao()->getTag2ByLikeName($name);
    }

    public function findAllTag2Groups($start, $limit)
    {
        return $this->getTag2GroupDao()->findAllTag2Groups($start, $limit);
    }

    public function getAll2GroupCount()
    {
        return $this->getTag2GroupDao()->findAllTag2GroupsCount();
    }

    public function findAllTags()
    {
        return $this->getTag2Dao()->findAllTags();
    }

    public function findAllTagGroups()
    {
        return $this->getTag2GroupDao()->findAllTagGroups();
    }

    public function findTagsByIds(array $ids)
    {
        return $this->getTag2Dao()->findTagsByIds($ids);
    }

    public function findTagsByNames(array $names)
    {
        return $this->getTag2Dao()->findTagsByNames($names);
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
        $disabledTagGroup = $this->getTag2GroupDao()->getDisabledTag2GroupByName($tagGroup['name']);

        if(!empty($disabledTagGroup)) {
            $fields = array (
                'type' => $tagGroup['type'],
                'name' => $disabledTagGroup['name'],
                'disabled' => '0',
                'createdTime' => time()
            );
            $tagGroup = $this->getTag2GroupDao()->updateTag2Group($disabledTagGroup['id'], $fields);
        } else {
            $tagGroup['createdTime'] = time();
            $tagGroup = $this->getTag2GroupDao()->addTag2Group($tagGroup);
        }

        $this->getLogService()->info('tagGroup', 'create', "添加标签组{$tagGroup['name']}(#{$tagGroup['id']})");

        return $tagGroup;
    }

    public function addTag($tag,$groupId)
    {
        $disabledTag = $this->getTag2Dao()->getDisabledTag2ByName($tag);
        if(!empty($disabledTag)){
            $fields = array (
                'name' => $disabledTag['name'],
                'groupId' => $groupId,
                'disabled' => '0',
                'createdTime' => time()
            );
            $tag = $this->getTag2Dao()->updateTag2($disabledTag['id'], $fields);
        } else {
             $fields = array (
                'name' => $tag,
                'groupId' => $groupId,
                'createdTime' => time()
            );
            $tag = $this->getTag2Dao()->addTag2($fields);
        }
        return $tag;
    }

    public function findTagsByTagGroupIds($tagGroupIds)
    {
        return $this->getTag2Dao()->findTagsByTagGroupIds($tagGroupIds);
    }

    public function updateTagGroup($id, array $fields)
    {
        $tagGroup = $this->getTagGroup($id);
        if (empty($tagGroup)) {
            throw $this->createServiceException("标签组(#{$id})不存在，更新失败！");
        }

        $disabledTagGroup = $this->getTag2GroupDao()->getDisabledTag2GroupByName($fields['name']);

        if(!empty($disabledTagGroup)) {
            $fields = array (
                'type' => $tagGroup['type'],
                'name' => $fields['name'],
                'disabled' => '0',
                'createdTime' => $tagGroup['createdTime']
            );
            $this->getTag2GroupDao()->updateTagGroupToDisabled($tagGroup['id']);
            $this->getTag2Dao()->updateTag2sByGroupId($tagGroup['id'],$disabledTagGroup['id']);
            $tagGroup = $this->getTag2GroupDao()->updateTag2Group($disabledTagGroup['id'], $fields);
        } else {
            $fields = ArrayToolkit::parts($fields, array('name','type'));
            $tagGroup = $this->getTag2GroupDao()->updateTag2Group($id, $fields);
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

        $disabledTag = $this->getTag2Dao()->getDisabledTag2ByName($fields['name']);

        if(!empty($disabledTag)){
            $fields = array (
                'name' => $disabledTag['name'],
                'groupId' => $tag['groupId'],
                'disabled' => '0',
                'createdTime' => $tag['createdTime']
            );
            $this->getTag2Dao()->updateTagToDisabled($id);
            $tag = $this->getTag2Dao()->updateTag2($disabledTag['id'], $fields);
        } else {
            $fields = ArrayToolkit::parts($fields, array('name'));
            $tag = $this->getTag2Dao()->updateTag2($id, $fields);
        }

        $this->getLogService()->info('tag', 'update', "编辑标签{$fields['name']}(#{$id})");

        return $tag;
    }

    public function deleteTagGroup($id)
    {
        $this->getTag2GroupDao()->updateTagGroupToDisabled($id);
        $this->getTag2Dao()->updateTagToDisabledByGroupId($id);

        $this->getLogService()->info('tagGroup', 'delete', "删除标签组#{$id}");
    }

    public function deleteTag($id)
    {
        $this->getTag2Dao()->updateTagToDisabled($id);
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

    private function getTag2Dao()
    {
        return $this->createDao('Tag.Tag2Dao');
    }

    private function getTag2GroupDao()
    {
        return $this->createDao('Tag.Tag2GroupDao');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}  