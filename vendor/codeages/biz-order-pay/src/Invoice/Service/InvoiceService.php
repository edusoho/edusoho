<?php
namespace Codeages\Biz\Invoice\Service;

interface InvoiceService
{
    public function getInvoice($id);

    public function getInvoiceBySn($sn);

    public function countInvoices($conditions);

    public function searchInvoices($conditions, $orderBy, $start, $limit);

    public function applyInvoice($apply);

    public function finishInvoice($id, $fields);
}