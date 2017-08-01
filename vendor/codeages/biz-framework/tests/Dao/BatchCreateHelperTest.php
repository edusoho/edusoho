<?php

namespace Tests;

use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use Tests\Example\Dao\AdvancedExampleDao;

class BatchCreateHelperTest extends IntegrationTestCase
{
    public function testFlush()
    {
        $helper = new BatchCreateHelper($this->getAdvancedExampleDao());
        for ($i = 1; $i <= 1000; ++$i) {
            $row = array(
                'name' => 'test'.$i,
                'content' => 'content',
            );
            $helper->add($row);
        }

        $helper->flush();

        $examples = $this->getAdvancedExampleDao()->search(array(), array(), 0, 1000);

        $this->assertCount(1000, $examples);
    }

    /**
     * @return AdvancedExampleDao
     */
    private function getAdvancedExampleDao()
    {
        return $this->biz->dao('Example:AdvancedExampleDao');
    }
}
