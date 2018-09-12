<?php

namespace Tests\Unit\AppBundle\Common;

use Biz\BaseTestCase;
use AppBundle\Common\ArrayToolkit;

class ArrayToolkitTest extends BaseTestCase
{
    public function testGet()
    {
        $testArray = array('id' => 1, 'name' => 'tom');
        $result = ArrayToolkit::get($testArray, 'id', 2);
        $this->assertEquals(1, $result);

        $result = ArrayToolkit::get($testArray, 'nickname', 2);
        $this->assertEquals(2, $result);
    }

    public function testColumn()
    {
        $testArray = array(
            array('id' => 1, 'name' => 'tom1'),
            array('id' => 2, 'name' => 'tom2'),
            array('id' => 3, 'name' => 'tom3'),
        );
        $result = ArrayToolkit::column($testArray, 'name');
        $this->assertEquals('tom1', $result[0]);
        $this->assertEquals('tom2', $result[1]);
        $this->assertEquals('tom3', $result[2]);

        $testArray = array();
        $result = ArrayToolkit::column($testArray, 'name');
        $this->assertEmpty($result);
    }

    public function testColumns()
    {
        $testArray = array(
            array('id' => 1, 'name' => 'tom1'),
            array('id' => 2, 'name' => 'tom2'),
            array('id' => 3, 'name' => 'tom3'),
        );
        $result = ArrayToolkit::columns($testArray, array('id', 'name'));
        $this->assertArrayEquals(array(1, 2, 3), $result[0]);
        $this->assertArrayEquals(array('tom1', 'tom2', 'tom3'), $result[1]);

        $testArray = array();
        $result = ArrayToolkit::columns($testArray, array('id', 'name'));
        $this->assertEmpty($result);
    }

    public function testParts()
    {
        $testArray = array('id' => 1, 'name' => 'tom', 'nickname' => 'jerry');
        $result = ArrayToolkit::parts($testArray, array('id', 'name'));
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertTrue(array_key_exists('name', $result));
        $this->assertTrue(!array_key_exists('nickname', $result));
    }

    public function testRequireds()
    {
        //1.非严格模式
        $testArray = array('id' => 1, 'name' => 'tom', 'createdTime' => '');
        $result = ArrayToolkit::requireds($testArray, array('id', 'name', 'createdTime'));
        $this->assertTrue($result);
        $result = ArrayToolkit::requireds($testArray, array('nickname'));
        $this->assertTrue(!$result);
        //2.严格模式
        $result = ArrayToolkit::requireds($testArray, array('id', 'name', 'createdTime'), true);
        $this->assertTrue(!$result);
    }

    public function testChanges()
    {
        $beforeArray = array('id' => 1, 'name' => 'tom1', 'createdTime' => '');
        $afterArray = array('id' => 1, 'nickname' => 'tom2', 'createdTime' => 15068832123);
        $result = ArrayToolkit::changes($beforeArray, $afterArray);
        $this->assertArrayEquals(array('createdTime' => ''), $result['before']);
        $this->assertArrayEquals(array('createdTime' => 15068832123), $result['after']);
    }

    public function testGroup()
    {
        $testArray = array(
            array('id' => 1, 'name' => 'tom1'),
            array('id' => 2, 'name' => 'tom1'),
            array('id' => 3, 'name' => 'tom2'),
            array('id' => 4, 'name' => 'tom2'),
            array('id' => 5, 'name' => 'tom3'),
            array('id' => 6, 'name' => 'tom3'),
        );
        $result = ArrayToolkit::group($testArray, 'name');
        $this->assertArrayEquals(array(
                array('id' => 1, 'name' => 'tom1'),
                array('id' => 2, 'name' => 'tom1'),
            ),
            $result['tom1']
        );
        $this->assertArrayEquals(array(
                array('id' => 3, 'name' => 'tom2'),
                array('id' => 4, 'name' => 'tom2'),
            ),
            $result['tom2']
        );
        $this->assertArrayEquals(array(
                array('id' => 5, 'name' => 'tom3'),
                array('id' => 6, 'name' => 'tom3'),
            ),
            $result['tom3']
        );
    }

    public function testIndex()
    {
        $testArray = array(
            array('id' => 1, 'name' => 'tom1'),
            array('id' => 2, 'name' => 'tom2'),
            array('id' => 3, 'name' => 'tom3'),
        );
        $result = ArrayToolkit::index($testArray, 'name');
        $this->assertArrayEquals(array(
                'tom1' => array('id' => 1, 'name' => 'tom1'),
                'tom2' => array('id' => 2, 'name' => 'tom2'),
                'tom3' => array('id' => 3, 'name' => 'tom3'),
            ),
            $result
        );
    }

    public function testGroupIndex()
    {
        $testArray = array(
            array('id' => 1, 'name' => 'tom1', 'nickname' => 'jerry1'),
            array('id' => 2, 'name' => 'tom2', 'nickname' => 'jerry2'),
            array('id' => 3, 'name' => 'tom3', 'nickname' => 'jerry3'),
        );
        $result = ArrayToolkit::groupIndex($testArray, 'name', 'nickname');
        $this->assertArrayEquals(array(
                'tom1' => array('jerry1' => array('id' => 1, 'name' => 'tom1', 'nickname' => 'jerry1')),
                'tom2' => array('jerry2' => array('id' => 2, 'name' => 'tom2', 'nickname' => 'jerry2')),
                'tom3' => array('jerry3' => array('id' => 3, 'name' => 'tom3', 'nickname' => 'jerry3')),
            ),
            $result
        );
    }

    public function testRename()
    {
        $testArray = array('id' => 1, 'name' => 'tom', 'nickname' => 'jerry');
        $result = ArrayToolkit::rename($testArray, array('name' => 'name1', 'nickname' => 'nickname1'));
        $this->assertArrayEquals(array(
            'id' => 1,
            'name1' => 'tom',
            'nickname1' => 'jerry',
        ), $result);
    }

    public function testFilter()
    {
        $testArray = array('id' => 1, 'price' => 0.01, 'isStudent' => true, 'name' => 'tom', 'nickname' => 'jerry');
        $specialArray = array('id' => 0, 'price' => 0.00, 'isStudent' => false, 'name' => 'tom2', 'createdTime' => 0);
        $result = ArrayToolkit::filter($testArray, $specialArray);
        $this->assertArrayEquals(array('id' => 1, 'price' => 0.01, 'isStudent' => true, 'name' => 'tom'), $result);
    }

    public function testTrim()
    {
        $testArray = array('name' => '   tom', 'names' => array('   tom1', '  tom2 '));
        $result = ArrayToolkit::trim($testArray);
        $this->assertArrayEquals(array('name' => 'tom', 'names' => array('tom1', 'tom2')), $result);
    }

    public function testEvery()
    {
        $testArray = array('name' => 'tom', 'nickname' => 'jerry');

        $result = ArrayToolkit::every($testArray, function ($value) {
            return is_string($value);
        });
        $this->assertTrue($result);
        $testArray = array('id' => 1, 'name' => 'tom', 'nickname' => 'jerry');
        $result = ArrayToolkit::every($testArray, function ($value) {
            return is_string($value);
        });
        $this->assertTrue(!$result);
    }

    public function testSome()
    {
        $testArray = array('name' => 'tom', 'nickname' => 'jerry');

        $result = ArrayToolkit::some($testArray, function ($value) {
            return is_string($value);
        });
        $this->assertTrue($result);
        $testArray = array('id' => 1);
        $result = ArrayToolkit::some($testArray, function ($value) {
            return is_string($value);
        });
        $this->assertTrue(!$result);
    }

    public function testMergeArraysValue()
    {
        $testArray = array(
            array(1, 2, 3),
            array(1, 2, 3, 4),
        );
        $result = ArrayToolkit::mergeArraysValue($testArray);
        $this->assertArrayEquals(array(1, 2, 3, 4), $result);
    }

    public function testThin()
    {
        $testArray = array(
            array('id' => 1, 'name' => 'tom1', 'nickname' => 'jerry1'),
            array('id' => 2, 'name' => 'tom2', 'nickname' => 'jerry2'),
            array('id' => 3, 'name' => 'tom3', 'nickname' => 'jerry3'),
        );
        $result = ArrayToolkit::thin($testArray, array('id', 'name'));
        $this->assertArrayEquals(array(
            array('id' => 1, 'name' => 'tom1'),
            array('id' => 2, 'name' => 'tom2'),
            array('id' => 3, 'name' => 'tom3'),
        ), $result);
    }

    public function testAppendKeyPrefix()
    {
        $array = array(
            'id' => 1,
            'name' => 'test',
        );

        $result = ArrayToolkit::appendKeyPrefix($array, 'user.');
        $this->assertArrayEquals(
            array(
                'user.id' => 1,
                'user.name' => 'test',
            ),
            $result
        );
    }

    public function testOrderByArrayWhenSuccessed()
    {
        // happy pass
        $array = array(1 => array('a', 'b', 'c'), 2 => array('d', 'e', 'f'), 3 => array('g', 'h', 'i'));
        $orderArray = array(3, 1, 2);
        $result = ArrayToolkit::orderByArray($array, $orderArray);
        $this->assertArrayEquals(
            array('g', 'h', 'i'),
            array_shift($result)
        );
    }

    public function testOrderByArrayWhenFailed()
    {
        // 长度不同
        $array = array(1 => array('a', 'b', 'c'), 2 => array('d', 'e', 'f'), 3 => array('g', 'h', 'i'));
        $orderArray = array(3, 1, 2, 3);
        $result = ArrayToolkit::orderByArray($array, $orderArray);
        $this->assertArrayEquals(
            array('a', 'b', 'c'),
            array_shift($result)
        );

        // 含有不同的数值
        $array = array(1 => array('a', 'b', 'c'), 2 => array('d', 'e', 'f'), 3 => array('g', 'h', 'i'));
        $orderArray = array(3, 1, 6);
        $result = ArrayToolkit::orderByArray($array, $orderArray);
        $this->assertArrayEquals(
            array('a', 'b', 'c'),
            array_shift($result)
        );

        // 含有相同重复的数值
        $array = array(1 => array('a', 'b', 'c'), 2 => array('d', 'e', 'f'), 3 => array('g', 'h', 'i'));
        $orderArray = array(3, 1, 3);
        $result = ArrayToolkit::orderByArray($array, $orderArray);
        $this->assertArrayEquals(
            array('a', 'b', 'c'),
            array_shift($result)
        );
    }

    public function testSortPerArrayValue()
    {
        $sortedBefore = array(
            array('id' => 1, 'title' => 'course1'),
            array('id' => 2, 'title' => 'course3'),
            array('id' => 3, 'title' => 'course2'),
        );

        $result = ArrayToolkit::sortPerArrayValue($sortedBefore, 'title');

        $this->assertEquals('course1', $result[0]['title']);
        $this->assertEquals('course3', $result[2]['title']);

        $result = ArrayToolkit::sortPerArrayValue($sortedBefore, 'title', false);
        $this->assertEquals('course3', $result[0]['title']);
        $this->assertEquals('course1', $result[2]['title']);
    }

    public function testIsSameValues()
    {
        $compared = array('a', 'b', 'c');

        $sameArr1 = array('b', 'c', 'a');
        $sameArr2 = array('k1' => 'b', 'k2' => 'c', 'k3' => 'a');

        $diffArr3 = array('a', 'b', 'c', 'd');
        $diffArr4 = array('a', 'b', 'd');

        $this->assertTrue(ArrayToolkit::isSameValues($compared, $sameArr1));
        $this->assertTrue(ArrayToolkit::isSameValues($compared, $sameArr2));
        $this->assertFalse(ArrayToolkit::isSameValues($compared, $diffArr3));
        $this->assertFalse(ArrayToolkit::isSameValues($compared, $diffArr4));
    }
}
