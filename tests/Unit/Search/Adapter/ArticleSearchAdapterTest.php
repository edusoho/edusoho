<?php

namespace Tests\Unit\Search\Adapter;

use Biz\BaseTestCase;
use Biz\Search\Adapter\SearchAdapterFactory;

class ArticleSearchAdapterTest extends BaseTestCase
{
    public function testAdapt()
    {
        $this->mockBiz(
            'Article:ArticleService',
            array(
                array(
                    'functionName' => 'getArticle',
                    'returnValue' => array('id' => 111, 'publishedTime' => 60000, 'thumb' => 'thumb'),
                    'withParams' => array(111),
                ),
            )
        );
        $result = SearchAdapterFactory::create('article')->adapt(array(array(
            'articleId' => 111,
            'content' => 'test',
            'category' => 'category',
            'updatedTime' => 500000,
        )));

        $this->assertEquals(60000, $result[0]['publishedTime']);
    }

    public function testAdaptWithEmptyArticle()
    {
        $this->mockBiz(
            'Article:ArticleService',
            array(
                array(
                    'functionName' => 'getArticle',
                    'returnValue' => array(),
                    'withParams' => array(111),
                ),
            )
        );
        $result = SearchAdapterFactory::create('article')->adapt(array(array(
            'articleId' => 111,
            'content' => 'test',
            'category' => 'category',
            'updatedTime' => 500000,
        )));

        $this->assertEquals(500000, $result[0]['publishedTime']);
    }
}
