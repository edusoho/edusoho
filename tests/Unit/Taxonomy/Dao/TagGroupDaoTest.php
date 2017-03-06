<?php

namespace Tests\Unit\Taxonomy\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TagGroupDaoTest extends BaseDaoTestCase
{
    public function testFindTagGroupByName()
    {
        $this->getDao()->create($this->getDefaultMockFields());
        $existTagGroup = $this->getDao()->findTagGroupByName('default');
        $noExistTagGroup = $this->getDao()->findTagGroupByName('lala');
        $this->assertNotNull($existTagGroup);
        $this->assertNull($noExistTagGroup);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'name' => 'default',
            'tagNum' => '1',
        );
    }
}
