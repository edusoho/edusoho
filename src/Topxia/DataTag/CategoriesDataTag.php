<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CategoriesDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取所有分类
     * 
     * 可传入的参数：
     *
     *   group 分类组CODE
     * 
     * @param  array $arguments 参数
     * @return array 分类
     */
    
    public function getData(array $arguments)
    {
        $this->checkGroupId($arguments);

        $group = $this->getCategoryService()->getGroupByCode($arguments['group']);
        if (empty($group)) {
            throw new \InvalidArgumentException("group:{$arguments['group']}不存在");
        }

    	return $this->getCategoryService()->findCategories($group['id']);
    }

}
