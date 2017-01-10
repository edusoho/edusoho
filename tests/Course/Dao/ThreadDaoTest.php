<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class ThreadDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        ;
    }

    private function getDefaultMockFields()
    {
        return array(
            
        );
    }

    private function mockThreadDao($fields)
    {
        return $this->getThreadDao()->create(array_merge($this->getDefaultMockFields(), $fields));
    }

    private function getThreadDao()
    {
        return $this->getBiz()->dao('Course:ThreadDao');
    }
}
