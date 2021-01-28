<?php

namespace Tests\Unit\DiscovieryColumn\Service;

use Biz\BaseTestCase;
use Biz\DiscoveryColumn\Service\DiscoveryColumnService;

class DiscoveryColumnServiceTest extends BaseTestCase
{
    public function testGetDiscoveryColumn()
    {
        $originDiscoveryColumn = $this->createDiscoveryColumn();
        $result = $this->getDiscoveryColumnService()->getDiscoveryColumn(1);
        $this->assertNull($this->getDiscoveryColumnService()->getDiscoveryColumn(2));
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('test', $result['title']);
        $this->assertEquals('course', $result['type']);
        $this->assertEquals(2, $result['categoryId']);
        $this->assertEquals('new', $result['orderType']);
        $this->assertEquals(4, $result['showCount']);
        $this->assertEquals(2, $result['seq']);
    }

    public function testUpdateDiscoveryColumn()
    {
        $origin = $this->createDiscoveryColumn();

        $result = $this->getDiscoveryColumnService()->updateDiscoveryColumn($origin['id'], array('categoryId' => 1));

        $origin['categoryId'] = 1;

        $this->assertEquals($origin['title'], $result['title']);
        $this->assertEquals(1, $result['categoryId']);
    }

    public function testAddDiscoveryColumn()
    {
        $origin = $this->createDiscoveryColumn();
        $this->assertEquals($origin['title'], 'test');
    }

    public function testDeleteDiscoveryColumn()
    {
        //$id
        $id = 2;
        $fields = array(
            'id' => $id,
            'title' => 'test',
            'type' => 'course',
            'categoryid' => '2',
            'ordertype' => 'new',
            'showCount' => '4',
            'seq' => '2',
        );
        $this->getDiscoveryColumnService()->addDiscoveryColumn($fields);
        $this->getDiscoveryColumnService()->deleteDiscoveryColumn($id);
        $result = $this->getDiscoveryColumnService()->getDiscoveryColumn($id);
        $this->assertEquals($result, null);
    }

    public function testFindDiscoveryColumnByTitle()
    {
        $origin = $this->createDiscoveryColumn();
        $resTitle = $this->getDiscoveryColumnService()->findDiscoveryColumnByTitle('test');
        $this->assertArrayEquals($origin, $resTitle[0]);
    }

    public function testGetAllDiscoveryColumns()
    {
        $result = $this->getDiscoveryColumnService()->getAllDiscoveryColumns();
        $this->assertEquals(count($result), 0);

        $origin = $this->createDiscoveryColumn();
        $result = $this->getDiscoveryColumnService()->getAllDiscoveryColumns();
        $this->assertArrayEquals(array($origin), $result);
    }

    public function testSortDiscoveryColumns()
    {
        $column1 = $this->createDiscoveryColumn();
        $column2 = $this->createDiscoveryColumn();
        $columnIds = array($column2['id'], $column1['id']);
        $this->getDiscoveryColumnService()->sortDiscoveryColumns($columnIds);
        $result = $this->getDiscoveryColumnService()->getAllDiscoveryColumns();
        $sortedColumn1 = $this->getDiscoveryColumnService()->getDiscoveryColumn($column1['id']);
        $sortedColumn2 = $this->getDiscoveryColumnService()->getDiscoveryColumn($column2['id']);
        $this->assertArrayEquals($result, array($sortedColumn2, $sortedColumn1));
    }

    public function testGetDisplayData()
    {
        $this->createDiscoveryColumn();
        $fakeCourseSets = array(
            array('id' => 1, 'title' => '123'),
            array('id' => 2, 'title' => '456'),
        );
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'searchCourseSets', 'returnValue' => $fakeCourseSets),
        ));
        $columns = $this->getDiscoveryColumnService()->getDisplayData();

        $this->assertEquals($fakeCourseSets, $columns[0]['data']);
        $this->assertEquals(2, $columns[0]['actualCount']);
        $fields = array(
            'title' => 'test',
            'type' => 'live',
            'categoryId' => 2,
            'showCount' => 4,
            'seq' => 2,
            'orderType' => 'recommend',
        );

        $this->getDiscoveryColumnService()->addDiscoveryColumn($fields);
        $this->assertEquals($fakeCourseSets, $columns[0]['data']);
        $this->assertEquals(2, $columns[0]['actualCount']);

        $fields = array(
            'title' => 'test',
            'type' => 'classroom',
            'categoryId' => 2,
            'orderType' => 'new',
            'showCount' => 4,
            'seq' => 2,
        );

        $this->getDiscoveryColumnService()->addDiscoveryColumn($fields);
        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'searchClassrooms', 'returnValue' => $fakeCourseSets),
        ));
        $columns = $this->getDiscoveryColumnService()->getDisplayData();

        $this->assertEquals($fakeCourseSets, $columns[0]['data']);
        $this->assertEquals(2, $columns[0]['actualCount']);
    }

    private function createDiscoveryColumn()
    {
        $fields = array(
            'title' => 'test',
            'type' => 'course',
            'categoryId' => 2,
            'orderType' => 'new',
            'showCount' => 4,
            'seq' => 2,
        );

        return $this->getDiscoveryColumnService()->addDiscoveryColumn($fields);
    }

    /**
     * @return DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }
}
