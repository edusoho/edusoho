<?php
namespace Topxia\Service\DiscovieryColumn\Tests;

use Topxia\Service\Common\BaseTestCase;

class DiscoveryColumnServiceTest extends BaseTestCase
{
    public function testGetDiscoveryColumn()
    {
        $discovieryColumn = $this->addDiscoveryColumn();
        $result           = $this->getDisCoveryColumnService()->getDiscoveryColumn($discovieryColumn['id']);
        $this->assertEquals($result['id'], $discovieryColumn['id']);
    }

    public function testUpdateDiscoveryColumn()
    {
        $discovieryColumn = $this->addDiscoveryColumn();
        $fileds           = array(
            'title' => 'classroom'
        );
        $result = $this->getDisCoveryColumnService()->updateDiscoveryColumn($discovieryColumn['id'], $fileds);
        $this->assertEquals($result['id'], $discovieryColumn['id']);
        $this->assertEquals('classroom', $result['title']);
    }

    public function testAddDiscoveryColumn()
    {
        $fileds = array(
            'title'       => 'discovery',
            'type'        => 'test',
            'orderType'   => 'recommend',
            'showCount'   => 100,
            'createdTime' => time()
        );

        $result = $this->getDisCoveryColumnService()->addDiscoveryColumn($fileds);
        $this->assertEquals('test', $result['type']);
        $this->assertEquals('discovery', $result['title']);
        $this->assertEquals('recommend', $result['orderType']);
        $this->assertEquals(100, $result['showCount']);
    }

    public function testDeleteDiscoveryColumn()
    {
        $discovieryColumn = $this->addDiscoveryColumn();
        $result           = $this->getDisCoveryColumnService()->deleteDiscoveryColumn($discovieryColumn['id']);

        $this->assertEquals(1, $result);
        $result = $this->getDisCoveryColumnService()->deleteDiscoveryColumn($discovieryColumn['id']);
        $this->assertEquals(0, $result);

    }

    public function testFindDiscoveryColumnByTitle()
    {
        $discovieryColumn = $this->addDiscoveryColumn();
        $result           = $this->getDisCoveryColumnService()->findDiscoveryColumnByTitle($discovieryColumn['title']);

        $this->assertEquals($discovieryColumn, $result[0]);
    }

    public function testGetAllDiscoveryColumns()
    {
        $discovieryColumn = $this->addDiscoveryColumn();
        $result           = $this->getDisCoveryColumnService()->getAllDiscoveryColumns();
        $this->assertEquals(1, count($result));
    }

    public function testSortDiscoveryColumns()
    {
        $fileds = array(
            'title'       => 'discovery1',
            'type'        => 'test',
            'orderType'   => 'recommend',
            'showCount'   => 100,
            'createdTime' => time()
        );
        $createDiscovery = $this->getDisCoveryColumnService()->addDiscoveryColumn($fileds);

        $this->getDisCoveryColumnService()->sortDiscoveryColumns(array($createDiscovery['id']));
        $result = $this->getDisCoveryColumnService()->getDiscoveryColumn($createDiscovery['id']);

        $this->assertEquals(1, $result['seq']);

    }

    protected function addDiscoveryColumn()
    {
        $fileds = array(
            'title'       => 'discovery',
            'type'        => 'test',
            'orderType'   => 'recommend',
            'showCount'   => 100,
            'createdTime' => time()
        );

        $discovieryColumn = $this->getDisCoveryColumnService()->addDiscoveryColumn($fileds);
        return $discovieryColumn;
    }

    protected function getDisCoveryColumnService()
    {
        return $this->getServiceKernel()->createService('DiscoveryColumn.DiscoveryColumnService');
    }
}
