<?php

namespace AppBundle\Extensions\DataTag;

class TagDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取标签.
     *
     * 可传入的参数：
     *
     *   tagId : 标签Id
     *
     * @param array $arguments 参数
     *
     * @return array 标签
     */
    public function getData(array $arguments)
    {
        $this->checkTagId($arguments);
        $tag = $this->getTagService()->getTag($arguments['tagId']);

        return $tag;
    }

    protected function checkTagId(array $arguments)
    {
        if (empty($arguments['tagId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('参数缺失'));
        }
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:TagService');
    }
}
