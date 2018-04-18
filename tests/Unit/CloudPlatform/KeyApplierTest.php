<?php

namespace Tests\Unit\CloudPlatform;

use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Mockery;
use Biz\CloudPlatform\KeyApplier;

class KeyApplierTest extends BaseTestCase
{
    public function testApplyKey()
    {
        $applier = new KeyApplier();
        $result = $applier->applyKey($this->getCurrentUser());
        $this->assertTrue(!empty($result['accessKey']));
        $this->assertTrue(!empty($result['secretKey']));
    }
}