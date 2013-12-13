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
     *   categoriesId 分类组Id
     * 
     * @param  array $arguments 参数
     * @return array 分类
     */
    
    public function getData(array $arguments)
    {
        if (empty($arguments['group'])) {
            throw new \InvalidArgumentException("categoriesId参数缺失");
        }
        if ($arguments['group'] == "course") {
            $categoriesId = "1";
        }
    	return $this->getCategoryService()->findCategories($categoriesId);
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}



?>