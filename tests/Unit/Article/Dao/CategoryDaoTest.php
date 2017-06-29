<?php

namespace Tests\Unit\Article\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CategoryDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('code' => 1));
        $expected[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $expected[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $testCondition = array(
            array(
                'condition' => array('parentId' => 0),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('parentId' => 1),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetByParentId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('parentId' => 1, 'code' => 1));
        $expected[] = $this->mockDataObject(array('parentId' => 2, 'code' => 2));

        $res = array();
        $res[] = $this->getDao()->getByParentId(0);
        $res[] = $this->getDao()->getByParentId(1);
        $res[] = $this->getDao()->getByParentId(2);

        foreach ($expected as $key => $val) {
            $this->assertEquals($val, $res[$key]);
        }
    }

    public function testFindByParentId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('code' => 1));
        $expected[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $expected[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $this->sort($expected, array('weight' => 'ASC', 'id' => 'ASC'));

        $res = array();
        $res[] = $this->getDao()->findByParentId(0);
        $res[] = $this->getDao()->findByParentId(1);
        $res[] = $this->getDao()->findByParentId(2);

        $this->assertEquals(array($expected[0], $expected[1]), $res[0]);
        $this->assertEquals(array($expected[2]), $res[1]);
        $this->assertEquals(array($expected[3]), $res[2]);
    }

    public function testFindAllPublishedByParentId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('published' => 0));
        $expected[] = $this->mockDataObject(array('code' => 1));
        $expected[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $expected[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $this->sort($expected, array('weight' => 'ASC', 'id' => 'ASC'));

        $res = array();
        $res[] = $this->getDao()->findAllPublishedByParentId(0);
        $res[] = $this->getDao()->findAllPublishedByParentId(1);
        $res[] = $this->getDao()->findAllPublishedByParentId(2);

        foreach ($res as $key => $val) {
            $this->assertEquals(array($expected[$key + 1]), $val);
        }
    }

    public function testFindByCode()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('published' => 0));
        $expected[] = $this->mockDataObject(array('code' => 1));
        $expected[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $expected[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $res = array();
        $res[] = $this->getDao()->findByCode('varchar');
        $res[] = $this->getDao()->findByCode(1);
        $res[] = $this->getDao()->findByCode(2);
        $res[] = $this->getDao()->findByCode(3);

        foreach ($res as $key => $val) {
            $this->assertEquals($expected[$key], $val);
        }
    }

    //已被testSearch覆盖
    public function testSearchByParentId()
    {
    }

    public function testCountByParentId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('code' => 1));
        $expected[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $expected[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

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
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('code' => 1));
        $expected[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $expected[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $res = array();
        $res[] = $this->getDao()->findByIds(array());
        $res[] = $this->getDao()->findByIds(array(1));
        $res[] = $this->getDao()->findByIds(array(1, 2, 3, 4));

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($expected[0]), $res[1]);
        $this->assertEquals($expected, $res[2]);
    }

    public function testFindAll()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('code' => 1));
        $expected[] = $this->mockDataObject(array('parentId' => 1, 'code' => 2));
        $expected[] = $this->mockDataObject(array('parentId' => 2, 'code' => 3));

        $this->sort($expected, array('weight' => 'ASC', 'id' => 'ASC'));

        $res = $this->getDao()->findAll();

        $this->assertEquals($expected, $res);
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
