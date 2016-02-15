<?php
namespace Topxia\Service\Cash\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;


class CashOrdersDaoImplTest extends BaseTestCase
{

    public function testCloseOrders()
    {
       $time = time();
       $result = $this->getCashOrdersDao()->closeOrders($time);
    }

    protected function getCashOrdersDao()
    {
        return $this->getServiceKernel()->createDao('Cash.CashOrdersDao');
    }

}
