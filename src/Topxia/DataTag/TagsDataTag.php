<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class TagsDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取所有标签
     * 
     * 可传入的参数：
     *
     *   count : 标签数
     * 
     * @param  array $arguments 参数
     * @return array 标签
     */
    
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        $tags = $this->getTagService()->findAllTags(0, $arguments['count']);

        return $tags;
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

}
