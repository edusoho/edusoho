<?php

namespace Tests;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Tests\Example\Dao\AdvancedExampleDao;
use Codeages\Biz\Framework\Dao\IdGenerator\OrderedTimeUUIDGenerator;

/**
 * @requires PHP 5.5
 */
class UuidAdvancedExampleDaoImplTest extends IntegrationTestCase
{
    public function testBatchCreate()
    {
        $rows = array(
            array('name' => 'test 1'),
            array('name' => 'test 2'),
            array('name' => 'test 3'),
            array('name' => 'test 4'),
            array('name' => 'test 5'),
        );
        $this->getUuidAdvancedExampleDao()->batchCreate($rows);
        $rows = $this->getUuidAdvancedExampleDao()->search(array(), array('id' => 'asc'), 0, 10);
        $this->assertCount(5, $rows);

        foreach ($rows as $row) {
            # code...
        }


    }
    
    private function getUuidAdvancedExampleDao()
    {
        return $this->biz->dao('Example:UuidAdvancedExampleDao');
    }
}
