<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CategoriesDataTag extends BaseDataTag implements DataTag  
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
        if (empty($arguments['group'])) {
            throw new \InvalidArgumentException("group参数缺失");
        }

        $group = $this->getCategoryService()->getGroupByCode($arguments['group']);
        if (empty($group)) {
            throw new \InvalidArgumentException("group:{$arguments['group']}不存在");
        }

    	return $this->getCategoryService()->findCategories($group['id']);
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}



?>