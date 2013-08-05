<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Service\Taxonomy\TagService;
use Topxia\Service\Common\BaseService;

class TagServiceImpl extends BaseService implements TagService
{

    public function addTag(array $tag)
    {
        $tag = $this->filterTag($tag);
        $this->checkTag($tag);
        $this->checkTagName($tag['name']);
        $tag['createdTime'] = time();
        return $this->getTagDao()->addTag($tag);
    }

    public function updateTag($id, array $fields)
    {
        $fields = $this->filterTag($fields);
        $this->checkTag($fields);
        return $this->getTagDao()->updateTag($id, $fields);
    }

    public function getTag($id)
    {
        $id = (int) $id;
        return $this->getTagDao()->getTag($id);
    }

    public function getTagByName($name)
    {
        return $this->getTagDao()->findTagByName($name);
    }

	public function getAllTags($start, $limit)
	{
		return $this->getTagDao()->findAllTags($start, $limit);
	}

    public function getAllTagsCount()
    {
        return $this->getTagDao()->findAllTagsCount();
    }

    public function getTagsByIds(array $ids)
    {
    	return $this->getTagDao()->findTagsByIds($ids);
    }

    public function getTagsByNames(array $names)
    {
    	return $this->getTagDao()->findTagsByNames($names);
    }

    public function deleteTag($id)
    {
        return $this->getTagDao()->deleteTag($id);
    }

    private function filterTag(array $tag)
    {
        foreach ($tag as $key => $value) {
            if ($key != 'name') unset($tag[$key]);
        }
        return $tag;
    }

    private function checkTag(array $tag)
    {
        if (empty($tag['name'])) {
            throw $this->createServiceException('标签名称不能为空!');
        }
        if (mb_strlen($tag['name']) > 25) {
            throw $this->createServiceException('标签名称过长!');
        }
    }

    private function checkTagName($name)
    {
        $existTag = $this->getTagByName($name);
        if (!empty($existTag)) {
            throw $this->createServiceException('标签已存在!');
        }
    }

	private function getTagDao()
	{
        return $this->createDao('Taxonomy.TagDao');
	}

}  