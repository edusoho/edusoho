<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\Article\Service\ArticleService;
use Biz\BaseTestCase;

class PublishedArticlesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $category1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1',
            'code' => 'c1',
            'weight' => 1,
            'parentId' => 0,
        ));

        $category2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2',
            'code' => 'c2',
            'weight' => 1,
            'parentId' => $category1['id'],
        ));

        $category3 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 3',
            'code' => 'c3',
            'weight' => 1,
            'parentId' => 0,
        ));

        $article1 = $this->getArticleService()->createArticle(array(
            'title' => 'Article1',
            'categoryId' => $category1['id'],
            'featured' => 1,
            'body' => '',
            'thumb' => '',
            'originalThumb' => '',
            'source' => '',
            'sourceUrl' => '',
            'publishedTime' => '2015-05-12 09:58:04',
            'tags' => array(),
        ));

        $article2 = $this->getArticleService()->createArticle(array(
            'title' => 'Article2',
            'categoryId' => $category2['id'],
            'featured' => 1,
            'sticky' => 1,
            'body' => '',
            'thumb' => '',
            'originalThumb' => '',
            'source' => '',
            'sourceUrl' => '',
            'publishedTime' => '2015-05-12 09:58:05',
            'tags' => array(),
        ));
        $article3 = $this->getArticleService()->createArticle(array(
            'title' => 'Article2',
            'categoryId' => $category2['id'],
            'promoted' => 1,
            'body' => '',
            'thumb' => '',
            'originalThumb' => '',
            'source' => '',
            'sourceUrl' => '',
            'publishedTime' => '2015-05-12 09:58:06',
            'tags' => array(),
        ));
        $datatag = new AllArticlesDataTag();
        $articles = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($articles));
        $this->assertEquals(2, $articles[0]['id']);
    }

    /**
     * @return ArticleService
     */
    public function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article:ArticleService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article:CategoryService');
    }
}
