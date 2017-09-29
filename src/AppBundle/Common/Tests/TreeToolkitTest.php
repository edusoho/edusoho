<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 29/09/2017
 * Time: 3:18 PM
 */

namespace AppBundle\Common\Tests;

use AppBundle\Common\TreeToolkit;
use Biz\BaseTestCase;

class TreeToolkitTest extends BaseTestCase
{
    public function testMakeTree()
    {
        $trees = TreeToolkit::makeTree($this->mockArray(), 0, 'parentID');
        $rootTree = reset($trees);
        $this->assertEquals(1, count($trees));
        $this->assertEquals(1, count($rootTree['children']));
        $this->assertEquals(100, $rootTree['id']);
    }

    public function testMakeSortTree()
    {
        $trees = TreeToolkit::makeSortTree($this->mockSortArray(), 100, 'parentID', 'seq');
        $rootTree = reset($trees);
        //$this->assertEquals(2, count($trees));
        //$this->assertEquals(1, count($rootTree['children']));
        //$this->assertEquals(100, $rootTree['id']);
        var_dump(json_encode($trees));
    }

    protected function mockArray()
    {
        return array(
            array('id' => 100, 'parentID' => 0, 'name' => 'a'),
            array('id' => 101, 'parentID' => 100, 'name' => 'a'),
            array('id' => 102, 'parentID' => 101, 'name' => 'a'),
            array('id' => 103, 'parentID' => 101, 'name' => 'a'),
        );
    }

    protected function mockSortArray()
    {
        return array(
            array('id' => 10, 'parentID' => 0, 'name' => 'a', 'seq' => 2),
            array('id' => 100, 'parentID' => 0, 'name' => 'a', 'seq' => 1),
            array('id' => 101, 'parentID' => 100, 'name' => 'a', 'seq' => 5),
            array('id' => 102, 'parentID' => 101, 'name' => 'a', 'seq' => '2'),
            array('id' => 103, 'parentID' => 101, 'name' => 'a', 'seq' => '3'),
            array('id' => 103, 'parentID' => 101, 'name' => 'a', 'seq' => '1'),
        );
    }
}
