<?php
namespace Topxia\Service\MoneyCard\Tests;

use Topxia\Service\Common\BaseTestCase;

// TODO

class MoneyCardServiceTest extends BaseTestCase
{   

    public function testMonetCardXXX()
    {
       $this->assertNull(null);
    }

    private function getMoneyCardService()
    {
        return $this->getServiceKernel()->createService('MoneyCard.MoneyCardService');
    }

}