<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PopularArticlePostsDataTag;

class PopularArticlePostsDataTagTest extends BaseTestCase
{
    public function testGetDataCountEnough()
    {
        $this->mockBiz('Thread:ThreadService', array(
            array(
                'functionName' => 'searchPosts',
                'returnValue' => array(array('id' => 1, 'targetId' => 1, 'userId' => 1), array('id' => 2, 'targetId' => 1, 'userId' => 2), array('id' => 3, 'targetId' => 2, 'userId' => 3), array('id' => 4, 'targetId' => 3, 'userId' => 2), array('id' => 5, 'targetId' => 3, 'userId' => 3)),
            ),
        ));

        $this->mockBiz('Article:ArticleService', array(
            array(
                'functionName' => 'findArticlesByIds',
                'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2), 3 => array('id' => 3)),
            ),
        ));

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'findUsersByIds',
                'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 3)),
            ),
        ));

        $dataTag = new PopularArticlePostsDataTag();
        $data = $dataTag->getData(array('count' => 2));

        $this->assertEquals(2, count($data));
    }

    public function testGetDataCountNotEnough()
    {
        $limitCount = 4;
        $this->mockBiz('Thread:ThreadService', array(
            array(
                'functionName' => 'searchPosts',
                //'withParams' => array(array('targetType' => 'article', 'parentId' => 0, 'latest' => 'week'), array('ups' => 'DESC', 'createdTime' => 'DESC'), 0, $limitCount),
                'returnValue' => array(array('id' => 1, 'targetId' => 1, 'userId' => 1), array('id' => 2, 'targetId' => 2, 'userId' => 2)),
            ),

        ));

        $this->mockBiz('Article:ArticleService', array(
            array(
                'functionName' => 'findArticlesByIds',
                'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2), 3 => array('id' => 3)),
            ),
        ));

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'findUsersByIds',
                'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 3)),
            ),
        ));

        $dataTag = new PopularArticlePostsDataTag();
        $data = $dataTag->getData(array('count' => $limitCount));

        $this->assertEquals(4, count($data));
    }
}
