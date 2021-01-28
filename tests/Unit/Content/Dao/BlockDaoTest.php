<?php

namespace Tests\Unit\Content;

use Biz\BaseTestCase;

class BlockDaoTest extends BaseTestCase
{
    public function testGetByTemplateIdAndOrgId()
    {
        $this->getBlockDao()->create(
            array(
                'blockTemplateId' => 1,
                'orgId' => 22,
                'userId' => 2,
                'createdTime' => time(),
            )
        );

        $result = $this->getBlockDao()->getByTemplateIdAndOrgId(1, 22);
        $this->assertArrayEquals(
            array(
                'userId' => 2,
                'orgId' => 22,
                'blockTemplateId' => 1,
            ),
            $result
        );
    }

    public function testGetByTemplateId()
    {
        $this->getBlockDao()->create(
            array(
                'blockTemplateId' => 1,
                'orgId' => 22,
                'userId' => 2,
                'createdTime' => time(),
            )
        );

        $result = $this->getBlockDao()->getByTemplateId(1);
        $this->assertArrayEquals(
            array(
                'userId' => 2,
                'orgId' => 22,
                'blockTemplateId' => 1,
            ),
            $result
        );
    }

    protected function getBlockDao()
    {
        return $this->createDao('Content:BlockDao');
    }
}
