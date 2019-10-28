<?php

namespace Codeages\Biz\Pay\Service;

interface PayService
{
    public function findEnabledPayments();

    public function createTrade($data, $createPlatformTrade = true);

    public function closeTradesByOrderSn($orderSn, $excludeTradeSns = array());

    public function findTradesByIds($ids);

    public function findTradesByOrderSns($orderSns);

    public function applyRefundByTradeSn($tradeSn, $data = array());

    public function notifyPaid($payment, $data);

    public function notifyRefunded($payment, $data);

    public function notifyClosed($data);

    public function queryTradeFromPlatform($tradeSn);

    public function getTradeByTradeSn($tradeSn);

    public function findTradesByTradeSn($tradeSns);

    public function searchTrades($conditions, $orderBy, $start, $limit, $columns = array());

    public function countTrades($conditions);

    public function setTradeInvoiceSnById($id, $invoiceSn);

    public function getCreateTradeResultByTradeSnFromPlatform($tradeSn);

    public function rechargeByIap($data);

    public function getTradeByPlatformSn($platformSn);
}
