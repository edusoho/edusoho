<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CategoryMarksDataTag;

class CategoryMarksDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $arguments = array(
            'selectedCategory' => 'first',
            'selectedSubCategory' => 'second',
            'selectedthirdLevelCategory' => 'third',
        );
        $this->mockBiz(
            'Taxonomy:CategoryService',
            array(
                array(
                    'functionName' => 'getCategoryByCode',
                    'withParams' => array('first'),
                    'returnValue' => array('code' => 'first', 'title' => '一级分类'),
                ),
                array(
                    'functionName' => 'getCategoryByCode',
                    'withParams' => array('second'),
                    'returnValue' => array('code' => 'second', 'title' => '二级分类'),
                ),
                array(
                    'functionName' => 'getCategoryByCode',
                    'withParams' => array('third'),
                    'returnValue' => array(),
                ),
            )
        );
        $datatag = new CategoryMarksDataTag();
        $contents = $datatag->getData($arguments);
        $this->assertArrayEquals(array(
            array('code' => 'first', 'title' => '一级分类'),
            array('code' => 'second', 'title' => '二级分类'),
            array(),
        ), $contents);
    }
}
