<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\TreeToolkit;
use AppBundle\Common\ReflectionUtils;

class TreeToolkitTest extends BaseTestCase
{
    public function testMakeTree()
    {
        $treeToolkit = new TreeToolkit();
        $data = $this->getData();

        $result = $treeToolkit->makeTree($data, 'id');
        $this->assertEquals(3, $result[0]['id']);
        $this->assertEquals(20, $result[0]['children'][0]['id']);
        $this->assertEquals(7, $result[0]['children'][0]['children'][0]['id']);
        $this->assertEquals(10, $result[0]['children'][0]['children'][1]['id']);

        $this->assertArrayEquals(array(
                'id' => 100,
                'title' => 'title100',
                'parentId' => 0,
                'children' => array(),
            ), $result[1]);
    }

    public function testGenerateParentId()
    {
        $treeToolkit = new TreeToolkit();
        $data = $this->getData();
        $result = ReflectionUtils::invokeMethod($treeToolkit, 'generateParentId', array($data, 'id'));

        $this->assertEquals(3, $result);
    }

    public function testMakeParentTree()
    {
        $treeToolkit = new TreeToolkit();
        $data = $this->getData();
        $result = ReflectionUtils::invokeMethod($treeToolkit, 'makeParentTree', array($data, 'id', 0, 'parentId'));

        $this->assertArrayEquals(array(
            array(
                'id' => 3,
                'title' => 'title3',
                'parentId' => 0,
            ),
            array(
                'id' => 100,
                'title' => 'title100',
                'parentId' => 0,
            ),
        ), $result);
    }

    private function getData()
    {
        return array(
            array(
                'id' => 20,
                'title' => 'title20',
                'parentId' => 3,
            ),
            array(
                'id' => 3,
                'title' => 'title3',
                'parentId' => 0,
            ),
            array(
                'id' => 7,
                'title' => 'title7',
                'parentId' => 20,
            ),
            array(
                'id' => 10,
                'title' => 'title10',
                'parentId' => 20,
            ),
            array(
                'id' => 15,
                'title' => 'title15',
                'parentId' => 10,
            ),
            array(
                'id' => 100,
                'title' => 'title100',
                'parentId' => 0,
            ),
        );
    }
}
