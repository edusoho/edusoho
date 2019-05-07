<?php

namespace Codeages\Biz\Pay\Payment;


use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;

class IapGateway extends AbstractGateway
{
    public function converterNotify($data)
    {
        $data = ArrayToolkit::parts($data, array(
            'user_id',
            'amount',
            'receipt',
            'transaction_id',
            'is_sand_box'
        ));

        return $this->requestReceiptData($data);
    }

    private function requestReceiptData($notifyData)
    {
        $userId = $notifyData['user_id'];
        $amount = $notifyData['amount'];
        $receipt = $notifyData['receipt'];
        $transactionId = $notifyData['transaction_id'];
        $isSandbox = $notifyData['is_sand_box'];

        if ($isSandbox) {
            $endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';
        } else {
            $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';
        }

        $postData = json_encode(
            array('receipt-data' => $receipt)
        );

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);

        curl_close($ch);

        if ($errno != 0) {
            return array(
                array(
                    'msg' => '充值失败！'.$errno
                ),
                'failure'
            );
        }

        $data = json_decode($response, true);
        if (empty($data)) {
            return array(
                array(
                    'msg' => '充值验证失败'
                ),
                'failure'
            );
        }

        if ($data['status'] == 21007) {
            $notifyData['is_sand_box'] = true;
            return $this->requestReceiptData($notifyData);
        }

        $iapOptions = $this->getIapOptions();
        if (!empty($iapOptions['bundleId'])) {
            if (!empty($data['receipt']['bundle_id']) && ($data['receipt']['bundle_id'] != $iapOptions['bundleId'])) {
                return array(
                    array(
                        'msg' => '充值失败!',
                    ),
                    'failure',
                );
            }

            $mobileIapProduct = $iapOptions['product'];
            $products = $data['receipt']['in_app'];
            $amount = 0;
            if (!empty($products)) {
                foreach ($products as $product) {
                    if (empty($mobileIapProduct[$product['product_id']]['price'])) {
                        $this->getTargetlogService()->log(TargetlogService::INFO, 'iap_charge.error', '', '购买的商品id不存在', array($product));
                    } else {
                        $amount = $amount + $mobileIapProduct[$product['product_id']]['price'];
                    }
                }
            }
        }

        if (!isset($data['status']) || $data['status'] != 0) {
            return array(
                array(
                    'msg' => '充值失败！状态码 :'.$data['status']
                ),
                'failure'
            );
        }

        if ($data['status'] == 0) {
            if (isset($data['receipt']) && !empty($data['receipt']['in_app'])) {
                $inApp = false;

                if ($transactionId) {
                    foreach ($data['receipt']['in_app'] as $value) {
                        if (ArrayToolkit::requireds($value, array('transaction_id', 'quantity', 'product_id'))
                            && $value['transaction_id'] == $transactionId) {
                            $inApp = $value;
                            break;
                        }
                    }
                } else {
                    $inApp = $data['receipt']['in_app'][0];
                }

                if (!$inApp) {
                    return array(
                        array(
                            'msg' => 'receipt校验失败：找不到对应的transaction_id'
                        ),
                        'failure'
                    );
                }

                return array(
                    array(
                        'status' => 'paid',
                        'pay_amount' => $amount*100,
                        'cash_flow' => $inApp['transaction_id'],
                        'paid_time' => $inApp['purchase_date'],
                        'quantity' => $inApp['quantity'],
                        'product_id' => $inApp['product_id'],
                        'attach' => array(
                            'user_id' => $userId
                        )
                    ),
                    'success'
                );
            }
        }

        return array(
            array(),
            'failure'
        );
    }

    public function createTrade($data)
    {
        throw new AccessDeniedException('can not create trade with iap.');
    }

    public function applyRefund($data)
    {
        throw new AccessDeniedException('can not apply refund with iap.');
    }

    public function queryTrade($tradeSn)
    {
        return null;
    }

    public function converterRefundNotify($data)
    {
        throw new AccessDeniedException('can not convert refund notify with iap.');
    }

    protected function getIapOptions()
    {
        return $this->biz['iap.options'];
    }

    protected function getTargetLogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }
}