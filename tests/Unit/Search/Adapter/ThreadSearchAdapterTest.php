<?php

namespace Tests\Unit\Search\Adapter;

use Biz\BaseTestCase;
use Biz\Search\Adapter\SearchAdapterFactory;

class ThreadSearchAdapterTest extends BaseTestCase
{
    public function testAdapt()
    {
        $result = SearchAdapterFactory::create('thread')->adapt(array(
            array(
                'threadId' => 2,
            ),
            array(
                'threadId' => 3,
            ),
        ));
        $this->assertArrayEquals(
            array(
                array(
                    'threadId' => 2,
                    'id' => 2,
                ),
                array(
                    'threadId' => 3,
                    'id' => 3,
                ),
            ),
            $result
        );
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
