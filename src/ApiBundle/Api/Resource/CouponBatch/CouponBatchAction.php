<?php

namespace ApiBundle\Api\Resource\CouponBatch;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Coupon\CouponException;
use Biz\User\UserException;
use Biz\Common\CommonException;

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

    protected function receive($request, $token)
    {
        $userId = $request->request->get('userId');
        if (empty($userId)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw UserException::NOTFOUND_USER();
        }
        $batch = $this->getCouponBatchService()->getBatchByToken($token);
        if (empty($batch)) {
            throw CouponException::NOTFOUND_COUPON();
        }
        if (0 == $batch['unreceivedNum']) {
            throw CouponException::OVER_BATCH_LIMIT();
        }

        $result = $this->getCouponBatchService()->receiveCoupon($token, $userId, true);
        if (isset($result['code']) && 'failed' == $result['code']) {
            $exceptionMethod = $result['exception']['method'];
            throw $result['exception']['class']::$exceptionMethod();
        }

        return $this->getCouponBatchService()->getBatchByToken($token);
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->service('Coupon:CouponBatchService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
