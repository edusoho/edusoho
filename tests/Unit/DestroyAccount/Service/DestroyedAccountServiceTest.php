<?php

namespace Tests\Unit\DestroyAccount\Service;

use Biz\BaseTestCase;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;
use Biz\DestroyAccount\Service\DestroyedAccountService;

class DestroyedAccountServiceTest extends BaseTestCase
{
    public function testCreateDestroyedAccount()
    {
        $account = $this->createDestroyedAccount();

        $this->assertEquals('2', $account['userId']);
    }

    public function testGetDestroyedAccount()
    {
        $account = $this->createDestroyedAccount();
        $result = $this->getDestroyedAccountService()->getDestroyedAccount($account['id']);

        $this->assertEquals('2', $result['userId']);
    }

    public function testSearchDestroyedAccounts()
    {
        $this->createDestroyedAccount();
        $result = $this->getDestroyedAccountService()->searchDestroyedAccounts(array('nicknameLike' => 'test'), array(), 0, 10);

        $this->assertEquals(1, count($result));
    }

    public function testCountDestroyedAccounts()
    {
        $this->createDestroyedAccount();
        $result = $this->getDestroyedAccountService()->countDestroyedAccounts(array());

        $this->assertEquals(1, $result);
    }

    protected function createDestroyedAccount($account = array())
    {
        $fields = array(
            'userId' => 2,
            'nickname' => 'test',
            'recordId' => 2,
        );
        $fields = array_merge($fields, $account);

        return $this->getDestroyedAccountService()->createDestroyedAccount($fields);
    }

    /**
     * @return DestroyedAccountService
     */
    protected function getDestroyedAccountService()
    {
        return $this->createService('DestroyAccount:DestroyedAccountService');
    }
}