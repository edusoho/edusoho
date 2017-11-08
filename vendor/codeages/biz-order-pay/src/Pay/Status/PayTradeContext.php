<?php

namespace Codeages\Biz\Pay\Status;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class PayTradeContext
{
    protected $biz;
    protected $PayTrade;
    protected $status;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function setPayTrade($PayTrade)
    {
        $this->PayTrade = $PayTrade;
        $this->status = $this->biz["payment_trade_status.{$PayTrade['status']}"];

        $this->status->setPayTrade($PayTrade);
    }

    public function getPayTrade()
    {
        return $this->PayTrade;
    }

    public function getStatus()
    {
        return $this->status;
    }

    function __call($method, $arguments)
    {
        $status = $this->getNextStatusName($method);

        if (!method_exists($this->status, $method)) {
            throw new AccessDeniedException("can't change {$this->PayTrade['status']} to {$status}.");
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
            $this->biz['logger']->error($e);
            throw new ServiceException($e->getMessage());
        }

        $this->dispatch("payment_trade.{$status}", $orderRefund);
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

}
