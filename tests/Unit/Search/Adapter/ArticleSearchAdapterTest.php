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
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getArticle',
                    'returnValue' => array(),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
            )
        );
        $result1 = SearchAdapterFactory::create('article')->adapt(array(array(
            'articleId' => 111,
            'content' => 'test',
            'category' => 'category',
            'updatedTime' => 500000,
        )));
        $result2 = SearchAdapterFactory::create('article')->adapt(array(array(
            'articleId' => 111,
            'content' => 'test',
            'category' => 'category',
            'updatedTime' => 500000,
        )));

        $this->assertEquals(60000, $result1[0]['publishedTime']);
        $this->assertEquals(500000, $result2[0]['publishedTime']);
    }
}