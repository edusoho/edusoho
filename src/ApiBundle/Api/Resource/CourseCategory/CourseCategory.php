<?php

namespace ApiBundle\Api\Resource\CourseCategory;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Taxonomy\CategoryException;

class CourseCategory extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $group = $this->getCategoryService()->getGroupByCode('course');

        if (empty($group)) {
            CategoryException::NOTFOUND_GROUP();
        }

        $categories = $this->getCategoryService()->getCategoryStructureTree($group['id']);

        return ['data' => $categories];
    }

    protected function getCategoryService()
    {
        return $this->getBiz()->service('Taxonomy:CategoryService');
    }
}
