<?php

namespace Codeages\Biz\Invoice\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Invoice\Service\InvoiceService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

class InvoiceServiceImpl extends BaseService implements InvoiceService
{
    public function getInvoice($id)
    {
        return $this->getInvoiceDao()->get($id);
    }

    public function applyInvoice($apply)
    {
        $apply = $this->prepareApply($apply);

        $orders = $this->tryApplyInvoice($apply);

        try {
            $this->biz['db']->beginTransaction();

            //update my invoice template
            if (!empty($apply['templateId'])) {
                $this->getInvoiceTemplateService()->updateInvoiceTemplate($apply['templateId'], $apply);
            } else {
                $this->getInvoiceTemplateService()->createInvoiceTemplate($apply);
            }

            $apply = $this->createInvoice($apply);

            foreach ($orders as $order) {
                $this->getOrderService()->updateOrderInvoiceSnByOrderId($order['id'], $apply['sn']);
            }

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return $apply;
    }

    protected function prepareApply($apply)
    {
        $user = $this->biz['user'];
        $apply['user_id'] = $user['id'];

        $apply['orderIds'] = explode('|', $apply['orderIds']);

        $apply['money'] *= 100;

        $apply['sn'] = $this->generateSn();

        return $apply;
    }

    protected function generateSn()
    {
        return date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function tryApplyInvoice($apply)
    {
        $orders = $this->getOrderService()->findOrdersByIds($apply['orderIds']);

        $user = $this->biz['user'];

        $money = 0;
        foreach ($orders as $key => $order) {
            if ($user['id'] != $order['user_id']) {
                throw new AccessDeniedException('order owner is invalid');
            }

            if (!empty($order['invoice_sn'])) {
                throw new AccessDeniedException('order invoiced');
            }

            $money += $order['pay_amount'];
        }

        if ($apply['money'] != $money) {
            throw new AccessDeniedException('The application amount does not match the order amount');
        }

        return $orders;
    }

    protected function createInvoice($apply)
    {
        if (!ArrayToolkit::requireds($apply, array('title', 'type', 'address', 'phone', 'email', 'receiver', 'money', 'sn'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $apply = ArrayToolkit::parts($apply, array('title', 'type', 'taxpayer_identity', 'comment', 'address', 'phone', 'email', 'receiver', 'money', 'user_id', 'sn'));

        $apply = $this->getInvoiceDao()->create($apply);

        return $apply;
    }

    public function finishInvoice($id, $fields)
    {
        $finishFields = array(
            'status' => 'sent',
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
            'number' => '',
            'post_number' => '',
            'review_comment' => '',
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

    protected function getInvoiceDao()
    {
        return $this->biz->dao('Invoice:InvoiceDao');
    }

    protected function getInvoiceTemplateService()
    {
        return $this->biz->service('Invoice:InvoiceTemplateService');
    }
}
