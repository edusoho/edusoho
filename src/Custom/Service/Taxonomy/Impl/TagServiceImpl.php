<?php
namespace Custom\Service\Taxonomy\Impl;
use Topxia\Service\Taxonomy\Impl\TagServiceImpl as BaseTagServiceImpl ;
use Topxia\Service\Taxonomy\TagService;

use Topxia\Common\ArrayToolkit;

class TagServiceImpl extends BaseTagServiceImpl implements TagService
{

    public function addTag(array $tag)
    {
        $tag = ArrayToolkit::parts($tag, array('name','description'));

        $this->filterTagFields($tag);
        $tag['createdTime'] = time();

        $tag = $this->getTagDao()->addTag($tag);

        $this->getLogService()->info('tag', 'create', "添加标签{$tag['name']}(#{$tag['id']})");

        return $tag;
    }

    public function updateTag($id, array $fields)
    {
        $tag = $this->getTag($id);
        if (empty($tag)) {
            throw $this->createServiceException("标签(#{$id})不存在，更新失败！");
        }

        $fields = ArrayToolkit::parts($fields, array('name','description'));
        $this->filterTagFields($fields, $tag);

        $this->getLogService()->info('tag', 'update', "编辑标签{$fields['name']}(#{$id})");

        return $this->getTagDao()->updateTag($id, $fields);
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

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}  