<?php

namespace Tests\Article\Dao;

use Tests\Base\BaseDaoTestCase;

class CategoryDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('code' => 1));
        $factor[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $factor[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $testCondition = array(
            array(
                'condition' => array('parentId' => 0),
                'expectedResults' => array($factor[0], $factor[1]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('parentId' => 1),
                'expectedResults' => array($factor[2]),
                'expectedCount' => 1
            ),
        );

        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetByParentId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('parentId' => 1, 'code' => 1));
        $factor[] = $this->mockDataObject(array('parentId' => 2, 'code' => 2));

        $res = array();
        $res[] = $this->getDao()->getByParentId(0);
        $res[] = $this->getDao()->getByParentId(1);
        $res[] = $this->getDao()->getByParentId(2);

        foreach ($factor as $key => $val) {
            $this->assertEquals($val, $res[$key]);
        }
    }

    public function testFindByParentId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('code' => 1));
        $factor[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $factor[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $this->factorSort($factor, array('weight' => 'ASC', 'id' => 'ASC'));

        $res = array();
        $res[] = $this->getDao()->findByParentId(0);
        $res[] = $this->getDao()->findByParentId(1);
        $res[] = $this->getDao()->findByParentId(2);

        $this->assertEquals(array($factor[0], $factor[1]), $res[0]);
        $this->assertEquals(array($factor[2]), $res[1]);
        $this->assertEquals(array($factor[3]), $res[2]);
    }

    public function testFindAllPublishedByParentId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject(array('published' => 0));
        $factor[] = $this->mockDataObject(array('code' => 1));
        $factor[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $factor[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $this->factorSort($factor, array('weight' => 'ASC', 'id' => 'ASC'));

        $res = array();
        $res[] = $this->getDao()->findAllPublishedByParentId(0);
        $res[] = $this->getDao()->findAllPublishedByParentId(1);
        $res[] = $this->getDao()->findAllPublishedByParentId(2);

        foreach ($res as $key => $val) {
            $this->assertEquals(array($factor[$key + 1]), $val);
        }
    }

    public function testFindByCode()
    {
        $factor = array();
        $factor[] = $this->mockDataObject(array('published' => 0));
        $factor[] = $this->mockDataObject(array('code' => 1));
        $factor[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $factor[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $res = array();
        $res[] = $this->getDao()->findByCode('varchar');
        $res[] = $this->getDao()->findByCode(1);
        $res[] = $this->getDao()->findByCode(2);
        $res[] = $this->getDao()->findByCode(3);

        foreach ($res as $key => $val) {
            $this->assertEquals($factor[$key], $val);
        }
    }

    //已被testSearch覆盖
    public function testSearchByParentId()
    {
        ;
    }

    public function testCountByParentId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('code' => 1));
        $factor[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $factor[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $res = array();
        $res[] = $this->getDao()->countByParentId(0);
        $res[] = $this->getDao()->countByParentId(1);
        $res[] = $this->getDao()->countByParentId(2);

        $this->assertEquals(2, $res[0]);
        $this->assertEquals(1, $res[1]);
        $this->assertEquals(1, $res[2]);
    }

    public function testFindByIds()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('code' => 1));
        $factor[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $factor[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $res = array();
        $res[] = $this->getDao()->findByIds(array());
        $res[] = $this->getDao()->findByIds(array(1));
        $res[] = $this->getDao()->findByIds(array(1, 2, 3, 4));

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($factor[0]), $res[1]);
        $this->assertEquals($factor, $res[2]);
    }
    
    public function testFindAll()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('code' => 1));
        $factor[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $factor[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $this->factorSort($factor, array('weight' => 'ASC', 'id' => 'ASC'));

        $res = $this->getDao()->findAll();

        $this->assertEquals($factor, $res);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'name' => 'varchar',    // 栏目名称
            'code' => 'varchar',    // URL目录名称
            'weight' => 1,
            'publishArticle' => 1,    // 是否允许发布文章
            'seoTitle' => 'varchar',    // 栏目标题
            'seoKeyword' => 'varchar',    // SEO 关键字
            'seoDesc' => 'varchar',    // 栏目描述（SEO）
            'published' => 1,    // 是否启用（1：启用 0：停用)
            'parentId' => 0,
        );
    }
}
