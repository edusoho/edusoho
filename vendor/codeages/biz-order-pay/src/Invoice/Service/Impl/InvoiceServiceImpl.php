<?php

namespace Codeages\Biz\Invoice\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Invoice\Dao\InvoiceDao;
use Codeages\Biz\Invoice\Service\InvoiceService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

class InvoiceServiceImpl extends BaseService implements InvoiceService
{
    public function getInvoice($id)
    {
        return $this->getInvoiceDao()->get($id);
    }

    public function getInvoiceBySn($sn)
    {
        return $this->getInvoiceDao()->getBySn($sn);
    }

    public function applyInvoice($apply)
    {
        $apply = $this->prepareApply($apply);

        $trades = $this->tryApplyInvoice($apply);

        try {
            $this->biz['db']->beginTransaction();

            $apply = $this->createInvoice($apply);

            foreach ($trades as $trade) {
                $this->getPayTradeService()->setTradeInvoiceSnById($trade['id'], $apply['sn']);
            }

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return $apply;
    }

    public function getRefundActualAmount($trades)
    {
        $user = $this->biz['user'];
        $refundedTrades = array_filter($trades,function ($trade){
            return $trade['status'] == 'refunded';
        });
        $orders = $this->getOrderService()->findOrdersBySns(ArrayToolkit::column($refundedTrades,'order_sn'));
        $orders = ArrayToolkit::index($orders,'id');
        if(ArrayToolkit::column($orders,'id')){
            $orderRefunds = $this->getOrderRefundService()->searchRefunds(['user_id' => $user['id'], 'order_ids' => ArrayToolkit::column($orders,'id'), 'status' => 'refunded'], array(), 0, PHP_INT_MAX);
            if (!empty($orderRefunds)){
                foreach ($orderRefunds as &$orderRefund){
                    $order = $orders[$orderRefund['order_id']];
                    $orderRefund['order_sn'] = $order['sn'];
                }
                $orderRefundGroups= ArrayToolkit::group($orderRefunds,'order_sn');
                foreach ($trades as &$trade){
                    if (!empty($orderRefundGroups[$trade['order_sn']])){
                        $trade['cash_amount'] -=  array_sum(ArrayToolkit::column($orderRefundGroups[$trade['order_sn']],'refund_cash_amount'));
                    }
                }
            }
        }

        return $trades;
    }

    protected function prepareApply($apply)
    {
        $user = $this->biz['user'];
        $apply['user_id'] = $user['id'];

        $apply['ids'] = explode(',', $apply['ids']);

        $apply['money'] *= 100;
        $apply['money'] = intval(strval($apply['money']));

        $apply['sn'] = $this->generateSn();

        return $apply;
    }

    protected function generateSn()
    {
        return date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function tryApplyInvoice($apply)
    {
        $trades = $this->getPayTradeService()->findTradesByIds($apply['ids']);

        $user = $this->biz['user'];

        $money = 0;
        $trades = $this->getRefundActualAmount($trades);

        foreach ($trades as $key => $trade) {
            if ($user['id'] != $trade['user_id']) {
                throw new AccessDeniedException('order owner is invalid');
            }

            if (!empty($trade['invoice_sn'])) {
                $invoice = $this->getInvoiceBySn($trade['invoice_sn']);
                if ('refused' != $invoice['status']) {
                    throw new AccessDeniedException('order invoiced');
                }
            }

            $money += $trade['cash_amount'];
        }

        if ($apply['money'] != $money) {
            throw new AccessDeniedException('The application amount does not match the order amount');
        }

        return $trades;
    }

    protected function createInvoice($apply)
    {
        if (!ArrayToolkit::requireds($apply, array('title', 'type', 'money', 'sn'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $apply = ArrayToolkit::parts($apply, array('title', 'type', 'taxpayer_identity', 'content', 'comment', 'address', 'company_address', 'company_mobile', 'email', 'phone', 'receiver', 'money', 'user_id', 'sn', 'bank', 'account'));

        $apply = $this->getInvoiceDao()->create($apply);

        return $apply;
    }

    public function finishInvoice($id, $fields)
    {
        $finishFields = array(
            'review_user_id' => $this->biz['user']['id'],
        );

        $fields = array_merge($fields, $finishFields);

        return $this->updateInvoice($id, $fields);
    }

    protected function updateInvoice($id, $fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'title' => '',
            'taxpayer_identity' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'receiver' => '',
            'status' => 'unchecked',
            'review_user_id' => 0,
            'trade_sns' => array(),
            'number' => '',
            'post_number' => '',
            'post_name' => '',
            'refuse_comment' => '',
            'company_address' => '',
            'company_mobile' => '',
            'bank' => '',
            'account' => ','
        ));

        $invoice = $this->getInvoiceDao()->update($id, $fields);

        return $invoice;
    }

    public function countInvoices($conditions)
    {
        return $this->getInvoiceDao()->count($conditions);
    }

    public function searchInvoices($conditions, $orderBy, $start, $limit)
    {
        return $this->getInvoiceDao()->search($conditions, $orderBy, $start, $limit);
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    /**
     * @return \Codeages\Biz\Pay\Service\Impl\PayServiceImpl
     */
    protected function getPayTradeService()
    {
        return $this->biz->service('Pay:PayService');
    }

    /**
     * @return InvoiceDao
     */
    protected function getInvoiceDao()
    {
        return $this->biz->dao('Invoice:InvoiceDao');
    }

    /**
     * @return \Codeages\Biz\Invoice\Service\Impl\InvoiceTemplateServiceImpl
     */
    protected function getInvoiceTemplateService()
    {
        return $this->biz->service('Invoice:InvoiceTemplateService');
    }

    /**
     * @return \Codeages\Biz\Order\Service\Impl\OrderRefundServiceImpl
     */
    protected function getOrderRefundService()
    {
        return $this->biz->service('Order:OrderRefundService');
    }
}
