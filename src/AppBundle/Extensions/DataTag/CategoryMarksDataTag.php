<?php

namespace AppBundle\Extensions\DataTag;

class CategoryMarksDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $selectedCategory = $arguments['selectedCategory'];
        $selectedSubCategory = $arguments['selectedSubCategory'];
        $selectedthirdLevelCategory = $arguments['selectedthirdLevelCategory'];

        return array(
            0 => empty($selectedCategory) ? array() : $this->getCategoryService()->getCategoryByCode($selectedCategory),
            1 => empty($selectedSubCategory) ? array() : $this->getCategoryService()->getCategoryByCode($selectedSubCategory),
            2 => empty($selectedthirdLevelCategory) ? array() : $this->getCategoryService()->getCategoryByCode($selectedthirdLevelCategory),
        );
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
