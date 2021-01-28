<?php

namespace Tests\Unit\Content;

use Tests\Unit\Base\BaseDaoTestCase;

class ContentDaoTest extends BaseDaoTestCase
{
    public function testGetByAlias()
    {
        $create = $this->mockDataObject();
        $get = $this->getDao()->getByAlias($create['alias']);
        $this->assertEquals($create['alias'], $get['alias']);
    }

    public function testSearchCount()
    {
        $this->mockDataObject();
        $count = $this->getDao()->count(array(
            'keyword' => 'test content',
        ));

        $this->assertEquals(1, $count);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'title' => 'test content',
            'type' => 'page',
            'alias' => 'aboutus',
            'picture' => '',
            'template' => 'default',
            'status' => 'published',
            'userId' => 1,
            'createdTime' => time(),
        );
    }
}
