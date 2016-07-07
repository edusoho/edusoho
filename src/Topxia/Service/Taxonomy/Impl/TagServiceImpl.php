<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Taxonomy\TagService;

class TagServiceImpl extends BaseService implements TagService
{
    public function getTag($id)
    {
        return $this->getTagDao()->getTag($id);
    }

    public function getTagByName($name)
    {
        return $this->getTagDao()->getTagByName($name);
    }

    public function isUserlevelNameAvalieable($name, $exclude)
    {
    }

    public function getTagByLikeName($name)
    {
        return $this->getTagDao()->getTagByLikeName($name);
    }

    public function findAllTags($start, $limit)
    {
        return $this->getTagDao()->findAllTags($start, $limit);
    }

    public function getAllTagCount()
    {
        return $this->getTagDao()->findAllTagsCount();
    }

    public function searchTags($conditions, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);
        return $this->getTagDao()->searchTags($conditions, $start, $limit);
    }

    public function searchTagCount($conditions)
    {
        $conditions = $this->_prepareConditions($conditions);
        return $this->getTagDao()->searchTagCount($conditions);
    }

    private function _prepareConditions($conditions)
    {
        $magic = $this->getSettingService()->get('magic');

        if (isset($magic['enable_org']) && $magic['enable_org']) {
            $user                = $this->getCurrentUser();
            $conditions['orgId'] = !empty($user['org']) ? $user['org']['id'] : null;
        }

        return $conditions;
    }

    public function findTagsByIds(array $ids)
    {
        $tags = $this->getTagDao()->findTagsByIds($ids);
        return ArrayToolkit::index($tags, 'id');
    }

    public function findTagsByNames(array $names)
    {
        return $this->getTagDao()->findTagsByNames($names);
    }

    public function isTagNameAvalieable($name, $exclude = null)
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

    public function addTag(array $tag)
    {
        $tag = ArrayToolkit::parts($tag, array('name'));

        $tag                = $this->filterTagFields($tag);
        $tag['createdTime'] = time();
        $tag                = $this->setTagOrg($tag);
        $tag                = $this->getTagDao()->addTag($tag);

        $this->getLogService()->info('tag', 'create', "添加标签{$tag['name']}(#{$tag['id']})");

        return $tag;
    }

    protected function setTagOrg($tag)
    {
        $magic = $this->getSettingService()->get('magic');

        if (empty($magic['enable_org'])) {
            return $tag;
        }

        $user       = $this->getCurrentUser();
        $currentOrg = $user['org'];

        if(empty($currentOrg)){
            return $tag;
        }

        $tag['orgId']   = $currentOrg['id'];
        $tag['orgCode'] = $currentOrg['orgCode'];

        return $tag;
    }

    public function updateTag($id, array $fields)
    {
        $tag = $this->getTag($id);

        if (empty($tag)) {
            throw $this->createServiceException("标签(#{$id})不存在，更新失败！");
        }

        $fields = ArrayToolkit::parts($fields, array('name'));
        $this->filterTagFields($fields, $tag);

        $this->getLogService()->info('tag', 'update', "编辑标签{$fields['name']}(#{$id})");

        return $this->getTagDao()->updateTag($id, $fields);
    }

    public function deleteTag($id)
    {
        $this->getTagDao()->deleteTag($id);
        $this->dispatchEvent("tag.delete", array('tagId' => $id));
        $this->getLogService()->info('tag', 'delete', "编辑标签#{$id}");
    }

    protected function filterTagFields(&$tag, $relatedTag = null)
    {
        if (empty($tag['name'])) {
            throw $this->createServiceException('标签名不能为空，添加失败！');
        }

        $tag['name'] = (string) $tag['name'];

        $exclude = $relatedTag ? $relatedTag['name'] : null;

        if (!$this->isTagNameAvalieable($tag['name'], $exclude)) {
            throw $this->createServiceException('该标签名已存在，添加失败！');
        }

        return $tag;
    }

    protected function getTagDao()
    {
        return $this->createDao('Taxonomy.TagDao');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }
}
