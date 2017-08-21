<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class OrderRefundContext
{
    protected $biz;
    protected $orderRefund;
    protected $status;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function setOrderRefund($orderRefund)
    {
        $this->orderRefund = $orderRefund;
        $this->status = $this->biz["order_refund_status.{$orderRefund['status']}"];

        $this->status->setOrderRefund($orderRefund);
    }

    public function getOrderRefund()
    {
        return $this->orderRefund;
    }

    public function getStatus()
    {
        return $this->status;
    }

    function __call($method, $arguments)
    {
        $status = $this->getNextStatusName($method);
        $nextStatusProcessor = $this->biz["order_refund_status.{$status}"];

        if (!in_array($this->orderRefund['status'], $nextStatusProcessor->getPriorStatus())) {
            throw new AccessDeniedException("can't change {$this->orderRefund['status']} to {$status}.");
        }

        try {
            $this->biz['db']->beginTransaction();
            $orderRefund = call_user_func_array(array($this->status, $method), $arguments);
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

        $this->createOrderLog($orderRefund);
        $this->dispatch("order_refund.{$status}", $orderRefund);
        return $orderRefund;
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

    protected function createOrderLog($orderRefund, $dealData = array())
    {
        $orderLog = array(
            'order_refund_id' => $orderRefund['id'],
            'status' => $orderRefund['status'],
            'order_id' => $orderRefund['order_id'],
            'user_id' => $this->biz['user']['id'],
            'deal_data' => $dealData
        );
        return $this->getOrderLogDao()->create($orderLog);
    }

    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    protected function dispatch($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    protected function getOrderLogDao()
    {
        return $this->biz->dao('Order:OrderLogDao');
    }
}
