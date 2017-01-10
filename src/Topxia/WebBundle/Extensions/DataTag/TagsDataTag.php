<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class TagsDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取所有标签
     *
     * 可传入的参数：
     *
     *   count : 标签数
     *   tagIds: 标签ids
     *
     * @param  array $arguments 参数
     * @return array 标签
     */

    public function getData(array $arguments)
    {
        $tags = array();

        if (isset($arguments['tags']) && !empty($arguments['tags'])) {
            $this->checkCount($arguments['tags']);
            $tags = $this->getTagService()->findTagsByIds($arguments['tags']['tagIds']);
        }

        return $tags;
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}
