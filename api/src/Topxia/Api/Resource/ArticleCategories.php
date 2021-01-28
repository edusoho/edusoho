<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ArticleCategories extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $categories = $this->getCategoryService()->getCategoryTree($isPublished = true);

        return $this->filter($categories);
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
