<?php

namespace ApiBundle\Api\Resource\Order\Factory;

use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Order\Service\WorkflowService;

abstract class BaseOrder
{
    /**
     * @var Biz
     */
    protected $biz;

    public function setBiz($biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    /**
     * @return WorkflowService
     */
    protected function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }

    /**
     * @return CurrentUser
     */
    protected function getCurrentUser()
    {
        $biz = $this->biz;

        return $biz['user'];
    }
}
