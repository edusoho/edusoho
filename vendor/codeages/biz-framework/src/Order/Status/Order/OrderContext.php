<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class OrderContext
{
    protected $biz;
    protected $order;
    protected $status;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        $this->status = $this->biz["order_status.{$order['status']}"];

        $this->status->setOrder($order);
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getStatus()
    {
        return $this->status;
    }

    function __call($method, $arguments)
    {
        $status = $this->getNextStatusName($method);

        if (!method_exists($this->status, $method)) {
            throw new AccessDeniedException("can't change {$this->order['status']} to {$status}.");
        }

        try {
            $this->biz['db']->beginTransaction();
            $order = call_user_func_array(array($this->status, $method), $arguments);
            $this->biz['db']->commit();
        } catch (AccessDeniedException $e) {
            $this->biz['db']->rollback();
            throw $e;
        } catch (InvalidArgumentException $e) {
            $this->biz['db']->rollback();
            throw $e;
        } catch (NotFoundException $e) {
            $this->biz['db']->rollback();
            throw $e;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw new ServiceException($e->getMessage());
        }

        $this->createOrderLog($order);
        $this->dispatch($status, $order);
        return $order;
    }

    private function getNextStatusName($method)
    {
        return $this->humpToLine($method);
    }

    private function humpToLine($str){
        $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
            return '_'.strtolower($matches[0]);
        },$str);

        if (strpos($str , '_') === 0) {
            return substr($str,1,strlen($str));
        }

        return $str;
    }

    protected function createOrderLog($order, $dealData = array())
    {
        $orderLog = array(
            'status' => $order['status'],
            'order_id' => $order['id'],
            'user_id' => $this->biz['user']['id'],
            'deal_data' => $dealData
        );
        return $this->getOrderLogDao()->create($orderLog);
    }

    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    protected function dispatch($status, $order)
    {
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        foreach ($orderItems as $orderItem) {
            $orderItem['order'] = $order;
            $this->getDispatcher()->dispatch("order.item.{$orderItem['target_type']}.{$status}", new Event($orderItem));
        }

        $deducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);
        foreach ($deducts as $deduct) {
            $deduct['order'] = $order;
            $this->getDispatcher()->dispatch("order.deduct.{$deduct['deduct_type']}.{$status}", new Event($deduct));
        }

        $order['items'] = $orderItems;
        $order['deducts'] = $deducts;
        return $this->getDispatcher()->dispatch("order.{$status}", new Event($order));
    }

    protected function getOrderLogDao()
    {
        return $this->biz->dao('Order:OrderLogDao');
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }
}
