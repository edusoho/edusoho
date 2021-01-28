<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\SelectedTagGroupsDataTag;
use Biz\BaseTestCase;

class SelectedTagGroupsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $dataTag = new SelectedTagGroupsDataTag();

        $groups = $dataTag->getData(array('tags' => array(1 => 1, 2 => 2)));
        $this->assertEquals(1, $groups[0]);
        $this->assertEquals(2, $groups[1]);
    }
}
