<?php

namespace ApiBundle\Api\Resource\Contract;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Contract\Service\ContractService;
use Biz\User\Service\UserService;

class ContractSign extends AbstractResource
{
    public function get(ApiRequest $request, $contractId)
    {
        $contract = $this->getContractService()->getContract($contractId);
        if (empty($contract)) {
            //抛异常
        }
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        if ('approved' === $user['approvalStatus']) {
            $userProfile = $this->getUserService()->getUserProfile($user['id']);
        }
        $signFields = [
            [
                'field' => 'truename',
                'default' => $userProfile['truename'] ?? '',
            ],
        ];
        foreach ($contract['sign'] as $field => $enable) {
            if (!empty($enable)) {
                $signFields[] = [
                    'field' => $field,
                    'default' => 'IDNumber' === $field ? ($userProfile['idcard'] ?? '') : '',
                ];
            }
        }

        return [
            'id' => $contractId,
            'name' => $contract['name'],
            'code' => date('Ymd').substr(microtime(true) * 10000, -6),
            'content' => $contract['content'],
            'seal' => $contract['seal'],
            'signFields' => $signFields,
            'signDate' => date('Y年m月d日'),
        ];
    }

    public function post(ApiRequest $request, $contractId)
    {
        $this->getContractService()->signContract($contractId, $request->request->all());

        return ['ok' => true];
    }

    /**
     * @return ContractService
     */
    private function getContractService()
    {
        return $this->service('Contract:ContractService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
