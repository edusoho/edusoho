<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CategoryDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取一个分类
     * 
     * 可传入的参数：
     *
     *   categoryId 必需 分类ID
     * 
     * @param  array $arguments 参数
     * @return array 分类
     */
    
    public function getData(array $arguments)
    {
        if (empty($arguments['categoryId'])) {
            throw new \InvalidArgumentException("categoryId参数缺失");
        }

    	return $this->getCategoryService()->getCategory($arguments['categoryId']);
    }


}
