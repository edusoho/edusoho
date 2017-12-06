<?php

namespace Tests\Unit\Thread;

use Biz\BaseTestCase;

/**
 * AbstractThreadFirewallTest
 */
class AbstractThreadFirewallTest extends BaseTestCase
{
    /**
     * @expectedException \UnderflowException
     * @expectedExceptionMessage Method accessThreadRead is not implement
     */
    public function testAccessThreadRead()
    {
        $stub = $this->mockAbstractThreadFirewall();
        $stub->accessThreadRead(array());
    }

    /**
     * @expectedException \UnderflowException
     * @expectedExceptionMessage Method accessThreadDelete is not implement
     */
    public function testAccessThreadDelete()
    {
        $stub = $this->mockAbstractThreadFirewall();
        $stub->accessThreadDelete(array());
    }

    /**
     * @expectedException \UnderflowException
     * @expectedExceptionMessage Method accessThreadUpdate is not implement
     */
    public function testAccessThreadUpdate()
    {
        $stub = $this->mockAbstractThreadFirewall();
        $stub->accessThreadUpdate(array());
    }

    /**
     * @expectedException \UnderflowException
     * @expectedExceptionMessage Method accessThreadSticky is not implement
     */
    public function testAccessThreadSticky()
    {
        $stub = $this->mockAbstractThreadFirewall();
        $stub->accessThreadSticky(array());
    }

    /**
     * @expectedException \UnderflowException
     * @expectedExceptionMessage Method accessThreadNice is not implement
     */
    public function testAccessThreadNice()
    {
        $stub = $this->mockAbstractThreadFirewall();
        $stub->accessThreadNice(array());
    }

    /**
     * @expectedException \UnderflowException
     * @expectedExceptionMessage Method accessPostCreate is not implement
     */
    public function testAccessPostCreate()
    {
        $stub = $this->mockAbstractThreadFirewall();
        $stub->accessPostCreate(array());
    }

    /**
     * @expectedException \UnderflowException
     * @expectedExceptionMessage Method accessPostUpdate is not implement
     */
    public function testAccessPostUpdate()
    {
        $stub = $this->mockAbstractThreadFirewall();
        $stub->accessPostUpdate(array());
    }

    /**
     * @expectedException \UnderflowException
     * @expectedExceptionMessage Method accessPostDelete is not implement
     */
    public function testAccessPostDelete()
    {
        $stub = $this->mockAbstractThreadFirewall();
        $stub->accessPostDelete(array());
    }

    public function mockAbstractThreadFirewall()
    {
        return $this->getMockForAbstractClass('Biz\Thread\Firewall\AbstractThreadFirewall');
    }
}
