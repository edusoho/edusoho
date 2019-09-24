<?php

namespace ApiBundle\Api\Resource\CouponBatch;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Coupon\CouponException;

class CouponBatchAction extends AbstractResource
{
    /**
     * @param ApiRequest $request
     *
     * @return mixed
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request, $token)
    {
        try {
            $action = $request->request->get('action');
            if (empty($action)) {
                throw CommonException::ERROR_PARAMETER_MISSING();
            }

            $function = $action;

            return \call_user_func(array($this, $function), $request, $token);
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'error' => array(
                    'code' => $e->getCode(),
                    'message' => $this->container->get('translator')->trans($e->getMessage()),
                ),
            );
        }
    }

    protected function batchReceive($request, $token)
    {
        $userIds = $request->request->get('userIds');
        if (empty($userIds)) {
            return $this->error('没有发券目标');
        }
        $batch = $this->getCouponBatchService()->getBatchByToken($token);
        if (empty($batch)) {
            return $this->error('该批次不存在');
        }
        if ($batch['unreceivedNum'] == 0 || $batch['unreceivedNum'] < count($userIds)) {
            return $this->error('该批次余量不足');
        }

        $this->getCouponBatchService()->
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->service('Coupon:CouponBatchService');
    }
}