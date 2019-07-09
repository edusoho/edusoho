<?php

namespace ApiBundle\Api\Resource\Coupon;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Coupon\Service\CouponService;
use CouponPlugin\Biz\Coupon\Service\CouponBatchService;

class CouponAction extends AbstractResource
{
    public function add(ApiRequest $request, $code)
    {
        try {
            $action = $request->request->get('action');

            if (empty($action)) {
                throw CommonException::ERROR_PARAMETER_MISSING();
            }

            $function = $action . "Coupon";

            return \call_user_func(array($this, $function), $request, $code);
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'error' => array(
                    'code' => $e->getCode(),
                    'message' => $this->container->get('translator')->trans($e->getMessage()),
                )
            );
        }
    }

    private function receiveCoupon($request, $code)
    {
        $id = $request->request->get('targetId');
        $type = $request->request->get('targetType');

        $coupon = $this->getCouponService()->getCouponByCode($code);

        if ($coupon['status'] == 'receive') {
            return $this->error(sprintf('优惠券%s已经被领取过', $code));
        }

        if ($this->isPluginInstalled('Coupon')) {
            $coupon = $this->getCouponService()->getCouponByCode($code);
            $batch = $this->getCouponBatchService()->getBatch($coupon['batchId']);
            if (empty($batch['h5MpsEnable'])) {
                $message = array('useable' => 'no', 'message' => '该优惠卷无法通过微网校渠道发放');

                return $this->error($message['message']);
            }
        }
        $result = $this->getCouponService()->checkCoupon($code, $id, $type);

        if (isset($result['useable']) && 'no' == $result['useable']) {
            return $this->error($result['message']);
        }

        return $this->success($result);
    }

    private function success($result)
    {
        return array(
            'success' => true,
            'message' => '领取成功，请在卡包中查看',
            'data' => $result,
        );
    }

    private function error($message)
    {
        return array(
            'success' => false,
            'error' => array(
                'code' => 500,
                'message' => $message,
            )
        );
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->service('CouponPlugin:Coupon:CouponBatchService');
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->service('Coupon:CouponService');
    }

}