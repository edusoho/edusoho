<?php

namespace Tests\Unit\User\Dao;

use Biz\BaseTestCase;

class StatusDaoImplTest extends BaseTestCase
{
    private function getStatusDao()
    {
        return $this->createDao('User:StatusDao');
    }
}
