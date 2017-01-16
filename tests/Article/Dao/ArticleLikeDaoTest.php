<?php

namespace Tests\Article\Dao;

use Tests\Base\BaseDaoTestCase;

class ArticleLikeDaoTest extends BaseDaoTestCase
{
    public function testGetByArticleIdAndUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('articleId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->getByArticleIdAndUserId(1, 1);
        $res[] = $this->getDao()->getByArticleIdAndUserId(2, 1);
        $res[] = $this->getDao()->getByArticleIdAndUserId(1, 2);

        foreach ($factor as $key => $val) {
            $this->assertEquals($val, $res[$key]);
        }
    }

    public function testDeleteByArticleIdAndUserId()
    {
        ;
    }

    public function testFindByUserId()
    {
        ;
    }

    public function testFindByArticleId()
    {
        ;
    }

    public function testFindByArticleIds()
    {
        ;
    }

    public function testFindByArticleIdsAndUserId()
    {
        ;
    }

    protected function getDefaultMockFields()
    {
        return array(
            'articleId' => 1,    // 资讯id
            'userId' => 1,    // 用户id
        );
    }
}
