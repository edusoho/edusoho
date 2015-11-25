<?php
namespace Topxia\Service\Search\Adapter;

class ArticleSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $articles)
    {
        $adaptResult = array();

        foreach ($articles as $index => $article) {
            $articleLocal             = $this->getArticleService()->getArticle($article['articleId']);
            $article['publishedTime'] = $articleLocal['publishedTime'];
            $article['thumb']         = $articleLocal['thumb'];
            $article['category']      = array('name' => $article['category']);
            array_push($adaptResult, $article);
        }

        return $adaptResult;
    }

    protected function getArticleService()
    {
        return $this->createService('Article.ArticleService');
    }
}
