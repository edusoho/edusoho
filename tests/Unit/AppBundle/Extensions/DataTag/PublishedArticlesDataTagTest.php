<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PublishedArticlesDataTag;

class PublishedArticlesDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentMissing()
    {
        $datatag = new PublishedArticlesDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new PublishedArticlesDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetDataEmpty()
    {
        $service = $this->mockBiz('Article:ArticleService', array(
            array(
                'functionName' => 'searchArticles',
                'returnValue' => array()
            )
        ));

        $dataTag = new PublishedArticlesDataTag();
        $data = $dataTag->getData(array('count' => 5));

        $this->assertEmpty($data);
        $service->shouldHaveReceived('searchArticles')->times(1);
    }

    public function testGetData()
    {
        $articleService = $this->mockBiz('Article:ArticleService', array(
            array(
                'functionName' => 'searchArticles',
                'returnValue' => array(array('id' => 1), array('id' => 2, 'categoryId' => 10), array('id' => 3, 'categoryId' => 10))
            )
        ));

        $categoryService = $this->mockBiz('Article:CategoryService', array(
            array(
                'functionName' => 'findCategoriesByIds',
                'returnValue' => array(10 => array('id' => 10))
            )
        ));

        $dataTag = new PublishedArticlesDataTag();
        $data = $dataTag->getData(array('count' => 5));

        $this->assertEquals(3, count($data));
        $this->assertArrayHasKey('category', $data[1]);
        $this->assertArrayNotHasKey('category', $data[0]);
        $articleService->shouldHaveReceived('searchArticles')->times(1);
        $categoryService->shouldHaveReceived('findCategoriesByIds')->times(1);
    }
}
