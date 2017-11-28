<?php

namespace Codeages\Biz\Order\Status\Order;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Order\Service\WorkflowService;
use Codeages\Biz\Order\Status\OrderStatusCallback;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

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

    public function created($data)
    {
        $this->status = $this->biz["order_status.created"];

        try {
            $this->biz['db']->beginTransaction();
            $order = $this->status->process($data);
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
            $this->biz['logger']->error($e);
            throw new ServiceException($e->getMessage());
        }

        $this->createOrderLog($order);
        $order = $this->dispatch('created', $order);
        $this->onOrderStatusChange('created', $order);

        return $order;
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
        $order = $this->dispatch($status, $order);
        $this->onOrderStatusChange($status, $order);

        return $order;
    }

    public function onOrderStatusChange($status, $order)
    {
        $orderItems = $order['items'];
        $deducts = $order['deducts'];
        unset($order['items']);
        unset($order['deducts']);

        $indexedOrderItems = ArrayToolkit::index($orderItems, 'id');

        $results = array();
        $method = 'on'.ucfirst($status);
        foreach ($deducts as $deduct) {
            $deduct['order'] = $order;
            if (!empty($indexedOrderItems[$deduct['item_id']])) {
                $deduct['item'] = $indexedOrderItems[$deduct['item_id']];
            }

            $processor = $this->getDeductCallback($deduct);
            if (!empty($processor) && $processor instanceof OrderStatusCallback && method_exists($processor, $method)) {
                $results[] = $processor->$method($deduct);
            }
        }

        foreach ($orderItems as $orderItem) {
            $orderItem['order'] = $order;

            $processor = $this->getProductCallback($orderItem);
            if (!empty($processor) && $processor instanceof OrderStatusCallback && method_exists($processor, $method)) {
                $results[] = $processor->$method($orderItem);
            }
        }

        $results = array_unique($results);
        if ($status == PaidOrderStatus::NAME) {
            if (in_array(OrderStatusCallback::SUCCESS, $results) && count($results) == 1) {
                $this->getWorkflowService()->finish($order['id']);
                if ($order['refund_deadline'] == 0) {
                    $this->getWorkflowService()->finished($order['id']);
                }
            } else if (count($results) > 0) {
                $this->getWorkflowService()->fail($order['id']);
            }
        }
    }

    protected function getProductCallback($orderItem)
    {
        $biz = $this->biz;

        if (empty($biz["order.product.{$orderItem['target_type']}"])) {
            return null;
        }
        return $biz["order.product.{$orderItem['target_type']}"];
    }

    protected function getDeductCallback($deduct)
    {
        $biz = $this->biz;

        if (empty($biz["order.deduct.{$deduct['deduct_type']}"])) {
            return null;
        }
        return $biz["order.deduct.{$deduct['deduct_type']}"];
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
            'status' => 'order.'.$order['status'],
            'order_id' => $order['id'],
            'user_id' => $this->biz['user']['id'],
            'deal_data' => $dealData,
            'ip' => empty($this->biz['user']['currentIp']) ? '' : $this->biz['user']['currentIp'],
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
        $indexedOrderItems = ArrayToolkit::index($orderItems, 'id');
        foreach ($orderItems as $orderItem) {
            $orderItem['order'] = $order;
            $event = new Event($orderItem);
            $this->getDispatcher()->dispatch("order.item.{$orderItem['target_type']}.{$status}", $event);
        }

        $deducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);
        foreach ($deducts as $deduct) {
            $deduct['order'] = $order;
            if (!empty($indexedOrderItems[$deduct['item_id']])) {
                $deduct['item'] = $indexedOrderItems[$deduct['item_id']];
            }
            $this->getDispatcher()->dispatch("order.deduct.{$deduct['deduct_type']}.{$status}", new Event($deduct));
        }

        $order['items'] = $orderItems;
        $order['deducts'] = $deducts;
        $this->getDispatcher()->dispatch("order.{$status}", new Event($order));

        return $order;
    }

    protected function getOrderLogDao()
    {
        return $this->biz->dao('Order:OrderLogDao');
    }

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
}
