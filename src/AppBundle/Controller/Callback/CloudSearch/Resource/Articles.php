<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;
use Biz\Article\Service\ArticleService;
use Biz\Article\Service\CategoryService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class Articles extends BaseProvider
{
    public function get(Request $request)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        $conditions['status'] = 'published';
        $conditions['updatedTime_GE'] = $conditions['cursor'];
        $articles = $this->getArticleService()->searchArticles($conditions, array('updatedTime' => 'ASC'), $start, $limit);
        $articles = $this->build($articles);
        $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $articles);

        return $this->wrap($this->filter($articles), $next);
    }

    public function build($articles)
    {
        $articles = $this->buildCategories($articles);
        $articles = $this->buildTags($articles);

        return $articles;
    }

    protected function buildCategories($articles)
    {
        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

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

    protected function buildTags($articles)
    {
        $articleIds = ArrayToolkit::column($articles, 'id');
        $tagIds = $this->getTagService()->findTagIdsByOwnerTypeAndOwnerIds('article', $articleIds);
        $groupTagIds = $this->getTagService()->findGroupTagIdsByOwnerTypeAndOwnerIds('article', $articleIds);

        $tags = $this->getTagService()->findTagsByIds($tagIds);
        foreach ($articles as &$article) {
            $articleTagIds = !empty($groupTagIds[$article['id']]) ? $groupTagIds[$article['id']] : array();
            if (!empty($articleTagIds)) {
                foreach ($articleTagIds as $index => $articleTagId) {
                    if (isset($tags[$articleTagId])) {
                        $article['tags'][$index] = array(
                            'id' => $tags[$articleTagId]['id'],
                            'name' => $tags[$articleTagId]['name'],
                        );
                    }
                }
            }
        }

        return $articles;
    }

    public function filter($res)
    {
        return $this->multicallFilter('article', $res);
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->getBiz()->service('Article:ArticleService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Article:CategoryService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->getBiz()->service('Taxonomy:TagService');
    }
}
