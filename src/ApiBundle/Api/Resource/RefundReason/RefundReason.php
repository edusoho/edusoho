<?php


namespace ApiBundle\Api\Resource\RefundReason;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class RefundReason extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $action = $request->query->get('action');
        $refundReason = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('refund_reason');
        return [
            'action' => $action,
            'refundReason' => $refundReason
        ];
    }
}