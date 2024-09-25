<?php

namespace Biz\Contract\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Content\FileTrait;
use Biz\Content\Service\FileService;
use Biz\Contract\ContractException;
use Biz\Contract\Dao\ContractDao;
use Biz\Contract\Dao\ContractGoodsRelationDao;
use Biz\Contract\Dao\ContractSignRecordDao;
use Biz\Contract\Dao\ContractSnapshotDao;
use Biz\Contract\Service\ContractService;
use Biz\System\Constant\LogAction;
use Biz\System\Constant\LogModule;
use Biz\System\Service\LogService;

class ContractServiceImpl extends BaseService implements ContractService
{
    use FileTrait;

    public function countContracts(array $conditions)
    {
        return $this->getContractDao()->count($conditions);
    }

    public function searchContracts(array $conditions, array $orderBys, $start, $limit, array $columns = [])
    {
        return $this->getContractDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function createContract(array $params)
    {
        $params = $this->preprocess($params);
        $params['createdUserId'] = $params['updatedUserId'] = $this->getCurrentUser()->getId();
        $contract = $this->getContractDao()->create($params);
        $this->getLogService()->info(LogModule::CONTRACT, LogAction::CREATE_CONTRACT, "创建了合同《{$contract['name']}》", $contract);
    }

    public function getContract($id)
    {
        $contract = $this->getContractDao()->get($id);
        if (!empty($contract)) {
            $contract['sealFile'] = $this->getFileService()->findFilesByUris([$contract['seal']])[0];
        }

        return $contract;
    }

    public function updateContract($id, array $params)
    {
        $params = $this->preprocess($params);
        $params['updatedUserId'] = $this->getCurrentUser()->getId();

        $this->getContractDao()->update($id, $params);
    }

    public function deleteContract($id)
    {
        $contract = $this->getContract($id);
        $this->getContractDao()->delete($id);
        $this->getLogService()->info(LogModule::CONTRACT, LogAction::DELETE_CONTRACT, "删除了合同《{$contract['name']}》", $contract);
    }

    public function generateContractCode()
    {
        return date('Ymd').substr(microtime(true) * 10000, -6);
    }

    public function signContract($id, $sign)
    {
        $requiredKeys = ['contractCode', 'goodsKey', 'truename'];
        $contract = $this->getContract($id);
        foreach ($contract['sign'] as $field => $enable) {
            if (!empty($enable)) {
                $requiredKeys[] = $field;
            }
        }
        if (!ArrayToolkit::requireds($sign, $requiredKeys, true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if ($this->getSignRecordByUserIdAndGoodsKey($this->getCurrentUser()->getId(), $sign['goodsKey'])) {
            throw ContractException::SIGN_RECORD_IS_EXISTED();
        }
        $sign = ArrayToolkit::parts($sign, $requiredKeys);
        $version = md5(json_encode([ArrayToolkit::parts($contract, ['name', 'content', 'seal'])]));
        $contractSnapshot = $this->getContractSnapshotDao()->getByVersion($version);
        if (empty($contractSnapshot)) {
            $contractSnapshot = $this->getContractSnapshotDao()->create([
                'name' => $contract['name'],
                'content' => $contract['content'],
                'seal' => $contract['seal'],
                'version' => $version,
            ]);
        }
        if (!empty($sign['handSignature'])) {
            if (0 !== strpos($sign['handSignature'], 'data:image/png;base64,')) {
                $sign['handSignature'] = 'data:image/png;base64,'.$sign['handSignature'];
            }
            $file = $this->fileDecode($sign['handSignature']);
            if (empty($file)) {
                throw CommonException::ERROR_PARAMETER();
            }
            $file = $this->getFileService()->uploadFile('user', $file);
            $sign['handSignature'] = $file['uri'];
        }
        $snapshot = [
            'contractCode' => $sign['contractCode'],
            'contractSnapshotId' => $contractSnapshot['id'],
            'sign' => ArrayToolkit::parts($sign, ['truename', 'IDNumber', 'phoneNumber', 'handSignature']),
        ];
        $this->getContractSignRecordDao()->create([
            'userId' => $this->getCurrentUser()->getId(),
            'goodsKey' => $sign['goodsKey'],
            'snapshot' => $snapshot,
        ]);
    }

    public function countSignedContracts(array $conditions)
    {
        return $this->getContractSignRecordDao()->count($conditions);
    }

    public function searchSignedContracts(array $conditions, array $orderBys, $start, $limit, array $columns = [])
    {
        return $this->getContractSignRecordDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function getSignedContract($id)
    {
        $signedContract = $this->getContractSignRecordDao()->get($id);
        $contractSnapshot = $this->getContractSnapshotDao()->get($signedContract['snapshot']['contractSnapshotId']);
        $signedContract['snapshot']['contract'] = $contractSnapshot;

        return $signedContract;
    }

    public function getRelatedContractByGoodsKey($goodsKey)
    {
        $relation = $this->getContractGoodsRelationDao()->getByGoodsKey($goodsKey);
        if (empty($relation)) {
            return null;
        }
        $contract = $this->getContract($relation['contractId']);
        if (empty($contract)) {
            return null;
        }
        $relation['contractName'] = $contract['name'];

        return $relation;
    }

    public function relateContract($id, $goodsKey, $forceSign)
    {
        $this->unRelateContract($goodsKey);
        $this->getContractGoodsRelationDao()->create([
            'goodsKey' => $goodsKey,
            'contractId' => $id,
            'sign' => empty($forceSign) ? 0 : 1,
        ]);
    }

    public function unRelateContract($goodsKey)
    {
        $relation = $this->getContractGoodsRelationDao()->getByGoodsKey($goodsKey);
        if ($relation) {
            $this->getContractGoodsRelationDao()->delete($relation['id']);
        }
    }

    public function findContractGoodsRelationsByContractIds($contractIds)
    {
        return $this->getContractGoodsRelationDao()->findByContractIds($contractIds);
    }

    public function getContractGoodsRelationByContractId($contractId)
    {
        return $this->getContractGoodsRelationDao()->getByContractId($contractId);
    }

    public function getSignRecordByUserIdAndGoodsKey($userId, $goodsKey)
    {
        return $this->getContractSignRecordDao()->getByUserIdAndGoodsKey($userId, $goodsKey);
    }

    public function findContractSnapshotsByIds($ids, $columns = [])
    {
        return $this->getContractSnapshotDao()->search(['ids' => $ids], [], 0, count($ids), $columns);
    }

    private function preprocess($params)
    {
        $keys = ['name', 'content', 'seal', 'sign'];
        if (!ArrayToolkit::requireds($params, $keys, true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $params = ArrayToolkit::parts($params, $keys);
        $signKeys = ['IDNumber', 'phoneNumber', 'handSignature'];
        if (!ArrayToolkit::requireds($params['sign'], $signKeys)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $params['sign'] = ArrayToolkit::parts($params['sign'], $signKeys);
        foreach ($signKeys as $signKey) {
            $params['sign'][$signKey] = empty($params['sign'][$signKey]) ? 0 : 1;
        }
        $file = $this->getFileService()->getFile($params['seal']);
        if (empty($file)) {
            throw CommonException::ERROR_PARAMETER();
        }
        $params['seal'] = $file['uri'];
        $params['content'] = $this->purifyHtml($params['content'], true);

        return $params;
    }

    /**
     * @return FileService
     */
    private function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return ContractDao
     */
    private function getContractDao()
    {
        return $this->createDao('Contract:ContractDao');
    }

    /**
     * @return ContractGoodsRelationDao
     */
    private function getContractGoodsRelationDao()
    {
        return $this->createDao('Contract:ContractGoodsRelationDao');
    }

    /**
     * @return ContractSnapshotDao
     */
    private function getContractSnapshotDao()
    {
        return $this->createDao('Contract:ContractSnapshotDao');
    }

    /**
     * @return ContractSignRecordDao
     */
    private function getContractSignRecordDao()
    {
        return $this->createDao('Contract:ContractSignRecordDao');
    }
}
