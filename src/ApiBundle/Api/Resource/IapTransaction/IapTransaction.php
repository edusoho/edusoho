<?php

namespace ApiBundle\Api\Resource\IapTransaction;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Codeages\Biz\Pay\Service\PayService;

class IapTransaction extends AbstractResource
{
    public function update(ApiRequest $request, $transactionId)
    {
        $user = $this->getCurrentUser()->toArray();
        $receipt = $request->request->get('receipt-data');
        $amount = $request->request->get('amount', 0);

        $data = array(
            'receipt' => $receipt,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'is_sand_box' => false,
            'user_id' => $user['id'],
        );

        $trade = $this->getPayService()->rechargeByIap($data);

        return array(
            'trade' => $trade,
        );
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->service('Pay:PayService');
    }
}
