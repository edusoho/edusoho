<?php

namespace Codeages\Biz\Invoice\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Invoice\Service\InvoiceTemplateService;

class InvoiceTemplateServiceImpl extends BaseService implements InvoiceTemplateService
{
    public function createInvoiceTemplate($invoice)
    {
        $this->validateInvoiceTemplateFields($invoice);

        $user = $this->biz['user'];
        $template = $this->getDefaultTemplate($user['id']);
        if (empty($template)) {
            $invoice['is_default'] = 1;
        }

        $invoice = $this->filterFields($invoice);

        return $this->getInvoiceTemplateDao()->create($invoice);
    }

    public function updateInvoiceTemplate($id, $invoice)
    {
        $this->validateInvoiceTemplateFields($invoice);
        $invoice = $this->filterFields($invoice);

        return $this->getInvoiceTemplateDao()->update($id, $invoice);
    }

    public function deleteInvoiceTemplate($invoiceId)
    {
        return $this->getInvoiceTemplateDao()->delete($invoiceId);
    }

    public function getInvoiceTemplate($id)
    {
        return $this->getInvoiceTemplateDao()->get($id);
    }

    public function searchInvoiceTemplates($conditions, $sort, $start, $limit)
    {
        return $this->getInvoiceTemplateDao()->search($conditions, $sort, $start, $limit);
    }

    public function countInvoiceTemplates($conditions)
    {
        return $this->getInvoiceTemplateDao()->count($conditions);
    }

    public function setDefaultTemplate($id)
    {
        $template = $this->getInvoiceTemplate($id);

        $userId = $template['user_id'];
        $defaultTemplate = $this->getDefaultTemplate($userId);
        if ($defaultTemplate) {
            $defaultTemplate['is_default'] = '0';
            $this->updateInvoiceTemplate($defaultTemplate['id'], $defaultTemplate);
        }

        $template['is_default'] = '1';

        return $this->updateInvoiceTemplate($id, $template);
    }

    public function getDefaultTemplate($userId)
    {
        return $this->getInvoiceTemplateDao()->getDefaultByUserId($userId);
    }

    protected function validateInvoiceTemplateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields,
            array(
                'title',
                'type',
                'taxpayer_identity',
            ))
        ) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'type',
                'taxpayer_identity',
                'content',
                'address',
                'phone',
                'email',
                'receiver',
                'comment',
                'user_id',
                'is_default',
                'company_address',
                'bank',
                'account',
                'company_mobile',
            )
        );
    }

    protected function getInvoiceTemplateDao()
    {
        return $this->biz->dao('Invoice:InvoiceTemplateDao');
    }
}
