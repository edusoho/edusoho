<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ArticleCategories extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $categoryIds = $this->getCategoryService()->findCategoryTreeIds(0, true);
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        return $this->filter(array_values($categories));
    }

    public function filter($res)
    {
        foreach ($res as $key => $one) {
            $res[$key]['createdTime'] = date('c', $one['createdTime']);
        }

        return $res;
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article:CategoryService');
    }
}
