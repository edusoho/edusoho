<?php
namespace Topxia\Service\MoneyCard\Tests;

use Topxia\Service\Common\BaseTestCase;

// TODO

class MoneyCardServiceTest extends BaseTestCase
{   

    private function getMoneyCardService()
    {
        return $this->getServiceKernel()->createService('MoneyCard.MoneyCardService');
    }

}