<?php

namespace ApiBundle\Api\Resource\Contract;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\Contract\Service\ContractService;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class Contract extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $this->checkPermission();
        $this->initContract();
        list($abort, $conditions) = $this->buildSearchConditions($request->query->all());
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        if ($abort) {
            return $this->makePagingObject([], 0, $offset, $limit);
        }
        $contracts = $this->getContractService()->searchContracts($conditions, ['updatedTime' => 'DESC'], $offset, $limit, ['id', 'name', 'updatedUserId', 'updatedTime']);

        return $this->makePagingObject($this->wrap($contracts), $this->getContractService()->countContracts($conditions), $offset, $limit);
    }

    public function add(ApiRequest $request)
    {
        $this->checkPermission();
        $this->getContractService()->createContract($request->request->all());

        return ['ok' => true];
    }

    public function get(ApiRequest $request, $id)
    {
        $contract = $this->getContractService()->getContract($id);
        $contract['seal'] = AssetHelper::getFurl($contract['seal']);
        if ($request->query->get('viewMode') == 'html') {
            $contract['content'] = str_replace("\n", '<br>', $contract['content']);
        }

        return $contract;
    }

    public function update(ApiRequest $request, $id)
    {
        $this->checkPermission();
        $this->getContractService()->updateContract($id, $request->request->all());

        return ['ok' => true];
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->checkPermission();
        $this->getContractService()->deleteContract($id);

        return ['ok' => true];
    }

    private function checkPermission()
    {
        if (!$this->getCurrentUser()->hasPermission('admin_v2_contract_manage')) {
            throw UserException::PERMISSION_DENIED();
        }
    }

    private function buildSearchConditions($query)
    {
        $conditions = [];
        if (empty($query['keywordType']) || (empty($query['keyword']) && '0' != $query['keyword'])) {
            return [false, $conditions];
        }
        if ('name' == $query['keywordType']) {
            $conditions['nameLike'] = $query['keyword'];
        }
        if ('username' == $query['keywordType']) {
            $users = $this->getUserService()->searchUsers(['nickname' => $query['keyword']], [], 0, PHP_INT_MAX, ['id']);
            if (empty($users)) {
                return [true, []];
            }
            $conditions['updatedUserIds'] = array_column($users, 'id');
        }

        return [false, $conditions];
    }

    private function wrap($contracts)
    {
        $wrappedContracts = [];
        $users = $this->getUserService()->findUsersByIds(array_column($contracts, 'updatedUserId'));
        $contractGoodsRelations = $this->getContractService()->findContractGoodsRelationsByContractIds(array_column($contracts, 'id'));
        $relatedGoodsCount = [];
        foreach ($contractGoodsRelations as $contractGoodsRelation) {
            $relatedGoodsCount[$contractGoodsRelation['contractId']] = $relatedGoodsCount[$contractGoodsRelation['contractId']] ?? [];
            list($goodsType) = explode('_', $contractGoodsRelation['goodsKey']);
            $relatedGoodsCount[$contractGoodsRelation['contractId']][$goodsType] = $relatedGoodsCount[$contractGoodsRelation['contractId']][$goodsType] ?? 0;
            ++$relatedGoodsCount[$contractGoodsRelation['contractId']][$goodsType];
        }
        foreach ($contracts as $contract) {
            $wrappedContracts[] = [
                'id' => $contract['id'],
                'name' => $contract['name'],
                'relatedGoodsCount' => $relatedGoodsCount[$contract['id']] ?? [],
                'updatedUser' => [
                    'nickname' => $users[$contract['updatedUserId']]['nickname'],
                ],
                'updatedTime' => $contract['updatedTime'],
            ];
        }

        return $wrappedContracts;
    }

    private function initContract()
    {
        if ($this->getEduCloudService()->isVisibleCloud()) {
            if (empty($this->getSettingService()->get('electronicContract'))) {
                $this->getSettingService()->set('electronicContract', ['enabled' => 1]);
            }
        }
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

    /**
     * @return EduCloudService
     */
    private function getEduCloudService()
    {
        return $this->service('CloudPlatform:EduCloudService');
    }
}
