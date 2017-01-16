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
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('articleId' => 2));

        $this->getDao()->deleteByArticleIdAndUserId(1, 1);

        $res = array();
        $res[] = $this->getDao()->getByArticleIdAndUserId(1, 1);
        $res[] = $this->getDao()->getByArticleIdAndUserId(2, 1);

        $this->assertEquals(null, $res[0]);
        $this->assertEquals($factor[1], $res[1]);
    }

    public function testFindByUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('articleId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $this->factorSort($factor, array('createdTime' => 'DESC', 'id' => 'ASC'));

        $res = array();
        $res[] = $this->getDao()->findByUserId(1);
        $res[] = $this->getDao()->findByUserId(2);

        
        
        $this->assertEquals(array($factor[0], $factor[1]), $res[0]);
        $this->assertEquals(array($factor[2]), $res[1]);
    }

    public function testFindByArticleId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('articleId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $this->factorSort($factor, array('createdTime' => 'DESC', 'id' => 'ASC'));

        $res = array();
        $res[] = $this->getDao()->findByArticleId(1);
        $res[] = $this->getDao()->findByArticleId(2);

        $this->factorSort($factor, array('createdTime' => 'DESC', 'id' => 'ASC'));
        
        $this->assertEquals(array($factor[0], $factor[2]), $res[0]);
        $this->assertEquals(array($factor[1]), $res[1]);
    }

    public function testFindByArticleIds()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('articleId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByArticleIds(array());
        $res[] = $this->getDao()->findByArticleIds(array(1));
        $res[] = $this->getDao()->findByArticleIds(array(1, 2));

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($factor[0], $factor[2]), $res[1]);
        $this->assertEquals($factor, $res[2]);
    }

    public function testFindByArticleIdsAndUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('articleId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByArticleIdsAndUserId(array(), 1);
        $res[] = $this->getDao()->findByArticleIdsAndUserId(array(2), 2);
        $res[] = $this->getDao()->findByArticleIdsAndUserId(array(1), 1);
        $res[] = $this->getDao()->findByArticleIdsAndUserId(array(1, 2), 1);

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array(), $res[1]);
        $this->assertEquals(array($factor[0]), $res[2]);
        $this->assertEquals(array($factor[0], $factor[1]), $res[3]);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'articleId' => 1,    // 资讯id
            'userId' => 1,    // 用户id
        );
    }
}
