<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Context\BizAware;
use Codeages\Biz\Order\Service\OrderService;

class OrderPayChecker extends BizAware
{
    /**
     * @var OrderPayCheckCommand[][]
     */
    private $commands;

    private $products = array();

    public function addCommand(OrderPayCheckCommand $command, $priority = 1)
    {
        $command->setBiz($this->biz);
        $command->setOrderPayChecker($this);

        $this->commands[] = array(
            'command' => $command,
            'priority' => $priority,
        );

        uasort($this->commands, function ($a1, $a2) {
            return $a1['priority'] < $a2['priority'];
        });
    }

    public function check($order, $params)
    {
        $commands = $this->commands;
        if (empty($commands)) {
            return;
        }

        foreach ($commands as $command) {
            $command['command']->execute($order, $params);
        }
    }

    public function getProducts($order)
    {
        if ($this->products) {
            return $this->products;
        }

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        foreach ($orderItems as $orderItem) {
            $this->products[] = $this->getOrderFacadeService()->getOrderProductByOrderItem($orderItem);
        }

        return $this->products;
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->biz->service('OrderFacade:OrderFacadeService');
    }
}
