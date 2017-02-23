<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Common\ArrayToolkit;

class CategoryMarksDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $selectedCategory           = $arguments['selectedCategory'];
        $selectedSubCategory        = $arguments['selectedSubCategory'];
        $selectedthirdLevelCategory = $arguments['selectedthirdLevelCategory'];
        
        return array(
            0 => $this->getCategoryService()->getCategoryByCode($selectedCategory),
            1 => $this->getCategoryService()->getCategoryByCode($selectedSubCategory),
            2 => $this->getCategoryService()->getCategoryByCode($selectedthirdLevelCategory)
        );
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
