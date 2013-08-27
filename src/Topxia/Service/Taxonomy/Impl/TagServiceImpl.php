<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Service\Taxonomy\TagService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

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

	public function findAllTags($start, $limit)
	{
		return $this->getTagDao()->findAllTags($start, $limit);
	}

    public function getAllTagCount()
    {
        return $this->getTagDao()->findAllTagsCount();
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

    public function addTag(array $tag)
    {
        $tag = ArrayToolkit::parts($tag, array('name'));

        $this->filterTagFields($tag);
        $tag['createdTime'] = time();

        return $this->getTagDao()->addTag($tag);
    }

    public function updateTag($id, array $fields)
    {
        $tag = $this->getTag($id);
        if (empty($tag)) {
            throw $this->createServiceException("标签(#{$id})不存在，更新失败！");
        }

        $fields = ArrayToolkit::parts($fields, array('name'));
        $this->filterTagFields($fields, $tag);

        return $this->getTagDao()->updateTag($id, $fields);
    }

    public function deleteTag($id)
    {
        $this->getTagDao()->deleteTag($id);
    }

    private function filterTagFields(&$tag, $relatedTag = null)
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

	private function getTagDao()
	{
        return $this->createDao('Taxonomy.TagDao');
	}

}  