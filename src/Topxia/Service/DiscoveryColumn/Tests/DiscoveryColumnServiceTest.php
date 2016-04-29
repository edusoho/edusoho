<?php

namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class CrontabServiceTest extends BaseTestCase
{
        public function testGetDiscoveryColumn()
        {
            $id = 0;
            $fields = array(
                'id' => $id,
                'title' => 'test',
                'type'    => 'course',
                'categoryid' => '2',
                'ordertype'  => 'new',
                'showCount' => '4',
                'seq' => '2',
                'createdTime' => time(),
                'updateTime' => time(),
            );
            $this->getDiscoveryColumnService()->addDiscoveryColumn($fields);
            $result = $this->getDiscoveryColumnService()->getDiscoveryColumn($id);
            $this->assertNull($result);
            $this->assertEquals(0,$result['id']]);
            $this->assertEquals('test',$result['title']]);
            $this->assertEquals('course',$result['type']]);
            $this->assertEquals(2,$result['categoryid']]);
            $this->assertEquals('new',$result['ordertype']]);
            $this->assertEquals(4,$result['showCount']]);
            $this->assertEquals(2,$result['seq']]);
            $this->assertNull($result['createdTime']);
            $this->assertNull($result['updateTime']);
   }

    public function testUpdateDiscoveryColumn()
    {

            $id = 0;
           $fields = array(
                'id' => $id,
                'title' => 'test',
                'type'    => 'course',
                'categoryid' => '2',
                'ordertype'  => 'new',
                'showCount' => '4',
                'seq' => '2',
                'createdTime' => time(),
                'updateTime' => time(),
            );
            $result = $this->getDiscoveryColumnService()->updateDiscoveryColumn($id,$fields);
            $this->assertNull($result);
            $this->assertEquals(0,$result['id']]);
            $this->assertEquals('test',$result['title']]);
            $this->assertEquals('course',$result['type']]);
            $this->assertEquals(2,$result['categoryid']]);
            $this->assertEquals('new',$result['ordertype']]);
            $this->assertEquals(4,$result['showCount']]);
            $this->assertEquals(2,$result['seq']]);
            $this->assertNull($result['createdTime']);
            $this->assertNull($result['updateTime']);
    }
    public function testAddDiscoveryColumn()
    {
            $id = 1;
            $fields = array(
                'id' => $id,
                'title' => 'test',
                'type'    => 'course',
                'categoryid' => '2',
                'ordertype'  => 'new',
                'showCount' => '4',
                'seq' => '2',
                'createdTime' => time(),
                'updateTime' => time(),
            );
            $this->getDiscoveryColumnService()->addDiscoveryColumn($fields);
            $result = $this->getDiscoveryColumnService()->getDiscoveryColumn($id);
            $this->assertNull($result);
            $this->assertEquals(0,$result['id']]);
            $this->assertEquals('test',$result['title']]);
            $this->assertEquals('course',$result['type']]);
            $this->assertEquals(2,$result['categoryid']]);
            $this->assertEquals('new',$result['ordertype']]);
            $this->assertEquals(4,$result['showCount']]);
            $this->assertEquals(2,$result['seq']]);
            $this->assertNull($result['createdTime']);
            $this->assertNull($result['updateTime']);
    }

    public function testDeleteDiscoveryColumn()
    {
        //$id
        $id = 2;
        $fields = array(
                'id' => $id,
                'title' => 'test',
                'type'    => 'course',
                'categoryid' => '2',
                'ordertype'  => 'new',
                'showCount' => '4',
                'seq' => '2',
                'createdTime' => time(),
                'updateTime' => time(),
            );
        $this->getDiscoveryColumnService()->addDiscoveryColumn($fields);
        $this->getDiscoveryColumnService()->deleteDiscoveryColumn($id);
        $result = $this->getDiscoveryColumn($id);
        $this->assertEquals($result,NULL);

    }

    public function testFindDiscoveryColumnByTitle()
    {
        $id = 4;
        $title = "test";
        $fields = array(
                'id' => $id,
                'title' => 'test',
                'type'    => 'course',
                'categoryid' => '2',
                'ordertype'  => 'new',
                'showCount' => '4',
                'seq' => '2',
                'createdTime' => time(),
                'updateTime' => time(),
            );
        $this->getDiscoveryColumnService()->addDiscoveryColumn($fields);
        $resTitle = $this->getDiscoveryColumnService()->findDiscoveryColumnByTitle();
        $this->assertEquals( $title , $resTitle );
        $this->assertEquals(4,$result['id']]);
        $this->assertEquals('test',$result['title']]);
        $this->assertEquals('course',$result['type']]);
        $this->assertEquals(2,$result['categoryid']]);
        $this->assertEquals('new',$result['ordertype']]);
        $this->assertEquals(4,$result['showCount']]);
        $this->assertEquals(2,$result['seq']]);
        $this->assertNull($result['createdTime']);
        $this->assertNull($result['updateTime']);
    }

    public function testGetAllDiscoveryColumns()
    {
        $result = $this->getDiscoveryColumnService()->getAllDiscoveryColumns();
        $this->assertNull($result);
    }

    public function testSortDiscoveryColumns()
    {
         $fields1 = array(
            'id' => 1,
            'title' => "title1",
            'type'    => 'course',
            'categoryid' => '2',
            'ordertype'  => 'new',
            'showCount' => '4',
            'seq' => '2',
            'createdTime' => time(),
            'updateTime' => time(),
        );
         $fields2 = array(
            'id' => 2,
            'title' => "title2",
            'type'    => 'course',
            'categoryid' => '2',
            'ordertype'  => 'new',
            'showCount' => '4',
            'seq' => '2',
            'createdTime' => time(),
            'updateTime' => time(),
        );
        $this->getDiscoveryColumnService()->addDiscoveryColumn($fields1);
        $this->getDiscoveryColumnService()->addDiscoveryColumn($fields2);
        $columnIds = array("1","2");
        $result = $this->getDiscoveryColumnService()->sortDiscoveryColumns($columnIds);
        $this->assertNull($result);
        $result1= $result[0];
        $result2= $result[1];
        $this->assertNull($result1);
        $this->assertNull($result2);

        $this->assertEquals(1,$result1['id']]);
        $this->assertEquals('title1',$result1['title']]);
        $this->assertEquals('course',$result1['type']]);
        $this->assertEquals(2,$result1['categoryid']]);
        $this->assertEquals('new',$result1['ordertype']]);
        $this->assertEquals(4,$result1['showCount']]);
        $this->assertEquals(2,$result1['seq']]);
        $this->assertNull($result1['createdTime']);
        $this->assertNull($result1['updateTime']);

        $this->assertEquals(2,$result2['id']]);
        $this->assertEquals('title2',$result2['title']]);
        $this->assertEquals('course',$result2['type']]);
        $this->assertEquals(2,$result2['categoryid']]);
        $this->assertEquals('new',$result2['ordertype']]);
        $this->assertEquals(4,$result2['showCount']]);
        $this->assertEquals(2,$result2['seq']]);
        $this->assertNull($result2['createdTime']);
        $this->assertNull($result2['updateTime']);

    }
}