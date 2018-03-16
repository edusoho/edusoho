<?php

namespace Tests\Unit\Article\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ArticleDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('status' => 'unpublished', 'promoted' => 0, 'picture' => 'int'));
        $expected[] = $this->mockDataObject(array('categoryId' => 2, 'sticky' => 0, 'orgCode' => 'char'));
        $expected[] = $this->mockDataObject(array('featured' => 0, 'title' => 'char', 'thumb' => 'int'));

        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('status' => 'published'),
                'expectedResults' => array($expected[1], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('articleIds' => array(1, 2)),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('categoryId' => 1),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('featured' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('promoted' => 1),
                'expectedResults' => array($expected[1], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('sticky' => 1),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('keywords' => 'char'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('pictureNull' => 'int'),
                'expectedResults' => array($expected[1], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('categoryIds' => array(1, 2)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('likeOrgCode' => 'var'),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('idNotEqual' => 3),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('articleId' => 1),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('thumbNotEqual' => 'int'),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('orgCode' => 'varchar'),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetPrevious()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $res = $this->getDao()->getPrevious(1, $expected[2]['createdTime'] + 1);

        $this->assertArrayEquals($expected[2], $res, $this->getCompareKeys());
    }

    public function testGetNext()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $res = $this->getDao()->getNext(1, $expected[0]['createdTime'] - 1);

        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $res = array();
        $res[] = $this->getDao()->findByIds(array());
        $res[] = $this->getDao()->findByIds(array(1));
        $res[] = $this->getDao()->findByIds(array(1, 2, 3));

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($expected[0]), $res[1]);
        $this->assertEquals($expected, $res[2]);
    }

    public function testFindAll()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $res = $this->getDao()->findAll();

        $this->assertEquals($expected, $res);
    }

    public function testSearchByCategoryIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('categoryId' => 2));

        $res = array();
        $res[] = $this->getDao()->searchByCategoryIds(array(1), 0, 10);
        $res[] = $this->getDao()->searchByCategoryIds(array(1, 2), 0, 10);

        $this->assertEquals(2, count($res[0]));
        foreach ($res[0] as $key => $value) {
            if ($expected[0]['id'] === $value['id']) {
                $this->assertArrayEquals($expected[0], $value);
            } elseif ($expected[1]['id'] === $value['id']) {
                $this->assertArrayEquals($expected[1], $value);
            } else {
                $this->assertTrue(false);
            }
        }
        $this->assertEquals($expected, $res[1]);
    }

    public function testCountByCategoryIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('categoryId' => 2));

        $res = array();
        $res[] = $this->getDao()->countByCategoryIds(array(1));
        $res[] = $this->getDao()->countByCategoryIds(array(1, 2));

        $this->assertEquals(2, $res[0]);
        $this->assertEquals(3, $res[1]);
    }

    public function testWaveArticle()
    {
        $expected = $this->mockDataObject();

        $this->getDao()->waveArticle(1, 'hits', 2);
        $expected['hits'] += 2;

        $res = current($this->getDao()->findByIds(array(1)));

        $this->assertArrayEquals($expected, $res, $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'title' => 'varchar', // 文章标题
            'categoryId' => 1, // 栏目
            'tagIds' => array('1', '2'), // tag标签
            'source' => 'varchar', // 来源
            'sourceUrl' => 'varchar', // 来源URL
            'publishedTime' => 1, // 发布时间
            'body' => 'text', // 正文
            'thumb' => 'varchar', // 缩略图
            'originalThumb' => 'varchar', // 缩略图原图
            'picture' => 'varchar', // 文章头图，文章编辑／添加时，自动取正文的第１张图
            'status' => 'published', // 状态
            'hits' => 1, // 点击量
            'featured' => 1, // 是否头条
            'promoted' => 1, // 推荐
            'sticky' => 1, // 是否置顶
            'postNum' => 1, // 回复数
            'upsNum' => 1, // 点赞数
            'userId' => 1, // 文章发布人的ID
            'orgId' => 1, // 组织机构ID
            'orgCode' => 'varchar', // 组织机构内部编码
        );
    }
}
