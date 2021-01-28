<?php

namespace Tests;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Tests\Example\Dao\AdvancedExampleDao;
use Codeages\Biz\Framework\Dao\IdGenerator\OrderedTimeUUIDGenerator;

/**
 * @requires PHP 5.5
 */
class UuidExampleDaoImplTest extends IntegrationTestCase
{
    public function testCreate()
    {
        $row = array('name' => 'test');
        $row = $this->getUuidExampleDao()->create($row);
        $this->assertArrayHasKey('id', $row);
    }
    
    private function getUuidExampleDao()
    {
        return $this->biz->dao('Example:UuidExampleDao');
    }
}
