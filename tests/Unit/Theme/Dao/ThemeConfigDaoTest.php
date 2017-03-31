<?php

namespace Tests\Unit\Theme\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ThemeConfigDaoTest extends BaseDaoTestCase
{

    public function testGetThemeConfigByName()
    {
        // $config = $this->mockDataObject();

        $config = $this->getDao()->getThemeConfigByName('简墨');
        $config = $this->getDao()->getThemeConfigByName('简墨');
        $config = $this->getDao()->getThemeConfigByName('简墨');
        $config = $this->getDao()->getThemeConfigByName('简墨');
        $config = $this->getDao()->getThemeConfigByName('简墨');
        var_dump($config);

    }

    protected function getDefaultMockFields()
    {
        return array(
            'name' => '简墨',
            'config' => array('a' => 'b'),
            'confirmConfig' => array('a' => 'b'),
            'allConfig' => array('a' => 'b'),
            'updatedUserId' => 1
        );
    }

}