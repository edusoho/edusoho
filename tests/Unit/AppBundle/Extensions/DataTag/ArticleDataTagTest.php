<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ArticleDataTag;

class ArticleDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $fields = array(
            'title' => 'article title',
            'body' => 'article body',
            'thumb' => '',
            'originalThumb' => '',
            'categoryId' => 1,
            'source' => 'article source',
            'publishedTime' => date('Y-m-d', strtotime('+1 day')),
        );
        $article = $this->getArticleService()->createArticle($fields);

        $dataTag = new ArticleDataTag();
        $data = $dataTag->getData(array('id' => $article['id']));
        $this->assertArrayEquals($article, $data);
    }

    private function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }
}
