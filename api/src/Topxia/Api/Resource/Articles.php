<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Articles extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        if (isset($conditions['cursor'])) {
            $conditions['status'] = 'published';
            $conditions['updatedTime_GE'] = $conditions['cursor'];
            $articles = $this->getArticleService()->searchArticles($conditions, array('updatedTime', 'ASC'), $start, $limit);
            $articles = $this->assemblyArticles($articles);
            $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $articles);
            return $this->wrap($this->filter($articles), $next);
        } else {
            $total = $this->getArticleService()->searchArticlesCount($conditions);
            $articles = $this->getArticleService()->searchArticles($conditions, array('publishedTime', 'DESC'), $start, $limit);
            return $this->wrap($this->filter($articles), $total);
        }
    }

    public function filter($res)
    {
        return $this->multicallFilter('Article', $res);
    }

    protected function assemblyArticles(&$articles)
    {
        $tagIds = array();
        foreach ($articles as $article) {
            $tagIds = array_merge($tagIds, $article['tagIds']);
        }

        $tags = $this->getTagService()->findTagsByIds($tagIds);

        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        foreach ($articles as &$article) {
            $article['tags'] = array();
            if (empty($article['tagIds'])) {
                continue;
            }
            foreach ($article['tagIds'] as $tagId) {
                if (empty($tags[$tagId])) {
                    continue;
                }
                $article['tags'][] = array(
                    'id' => $tagId,
                    'name' => $tags[$tagId]['name'],
                );
            }
        }

        foreach ($articles as &$article) {
            if (isset($categories[$article['categoryId']])) {
                $article['category'] = array(
                    'id' => $categories[$article['categoryId']]['id'],
                    'name' => $categories[$article['categoryId']]['name'],
                );
            } else {
                $article['category'] = array();
            }
        }

        return $articles;
    }

    protected function multicallFilter($name, $res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->callFilter($name, $one);
        }
        return $res;
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}
