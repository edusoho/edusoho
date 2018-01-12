<?php

namespace AppBundle\Common\Tests;

use AppBundle\Common\Tree;
use Biz\BaseTestCase;

class TreeTest extends BaseTestCase
{
    public function testBuildEmptyArray()
    {
        $tree = Tree::buildWithArray(array());
        $this->assertFalse($tree->hasChildren());
    }

    public function testBuildArray()
    {
        $tree = Tree::buildWithArray($this->getTestArray());

        foreach (range(1, 8) as $value) {
            $children = $tree->getChildren();
            if (isset($children[0])) {
                $tree = $children[0];
                $this->assertEquals($tree->data['id'], $value);
            }
        }
    }

    public function testTreeColumn()
    {
        $tree = Tree::buildWithArray($this->getTestArray());

        $expectArray = array(1, 2, 3, 4, 5, 6, 7);
        $this->assertArrayEquals($expectArray, $tree->column('id'));
    }

    public function testTreeReduce()
    {
        $tree = Tree::buildWithArray($this->getTestArray());

        $expect = 28;
        $this->assertEquals($expect, $tree->reduce(function ($ret, $tree) {
            return $ret + $tree->data['id'];
        }, 0));
    }

    public function testTreeFind()
    {
        $tree = Tree::buildWithArray($this->getTestArray());
        $expect = array(2, 3, 4, 5, 6, 7);
        $tree = $tree->find(function ($tree) {
            return 2 === $tree->data['id'];
        });

        $this->assertArrayEquals($tree->column('id'), $expect);
    }

    public function getTestArray()
    {
        return array(
            array(
                'id' => 1,
                'parentId' => 0,
            ),
            array(
                'id' => 2,
                'parentId' => 1,
            ),
            array(
                'id' => 3,
                'parentId' => 2,
            ),
            array(
                'id' => 4,
                'parentId' => 3,
            ),
            array(
                'id' => 5,
                'parentId' => 4,
            ),
            array(
                'id' => 6,
                'parentId' => 5,
            ),
            array(
                'id' => 7,
                'parentId' => 6,
            ),
        );
    }
}
