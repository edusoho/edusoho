<?php

namespace Biz\Contract\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Content\FileTrait;
use Biz\Content\Service\FileService;
use Biz\Contract\Dao\ContractDao;
use Biz\Contract\Dao\ContractGoodsRelationDao;
use Biz\Contract\Dao\ContractSignRecordDao;
use Biz\Contract\Dao\ContractSnapshotDao;
use Biz\Contract\Service\ContractService;
use Biz\User\Service\UserService;

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
        $this->getContractDao()->create($params);
    }

    public function getContract($id)
    {
        return $this->getContractDao()->get($id);
    }

    public function updateContract($id, array $params)
    {
        $params = $this->preprocess($params);
        $params['updatedUserId'] = $this->getCurrentUser()->getId();

        $this->getContractDao()->update($id, $params);
    }

    public function deleteContract($id)
    {
        $this->getContractDao()->delete($id);
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
        $content = $this->getDetailContent($signedContract['snapshot']['contract']['content'], $signedContract['goodsKey']);
        $this->getHtmlByRecord($content, $signedContract['sign']);

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

    public function getContractDetail($contract)
    {
        $contractGoodsRelation = $this->getContractGoodsRelationByContractId($contract['id']);
        $content = $this->getDetailContent($contract['content'], $contractGoodsRelation['goodsKey']);

        return $this->getHtml($content, $contract['sign'], $this->getCurrentUser());
    }

    private function getDetailContent($content, $goodsKey)
    {
        $parts = explode('_', $goodsKey);
        $product = $this->getServiceByType($parts[0])->get($parts[1]);
        $member = [];
        if ('course' == $parts[0]) {
            $member = $this->getMemberService($parts[0])->getCourseMember($parts[1], $this->getCurrentUser()->getId());
        } elseif ('classroom' == $parts[0]) {
            $member = $this->getMemberService($parts[0])->getClassroomMember($parts[1], $this->getCurrentUser()->getId());
        } else {
            $member = $this->getMemberService($parts[0])->getExerciseMember($parts[1], $this->getCurrentUser()->getId());
        }
        $order = $this->getOrderService()->getOrder($member['orderId']);
        $user = $this->getCurrentUser();

        return str_replace(
            ['$name$', '$username$', '$idcard$', '$courseName$', '$contract number$', '$date$', '$order price$'],
            [$user['truename'] ?? '', $user['nickname'] ?? '', $user['idcard'] ?? '', $product['title'] ?? '', $contract['id'] ?? '', date('Y年m月d日') ?? '', $order['pay_amount'] ?? ''],
            $content
        );
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

        return $params;
    }

    protected function getHtml($content, $sign, $user)
    {
//        return $htmlContent = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>合同页面</title></head><body style="padding: 20px 32px 20px 32px; min-width: 311px;"><p style="overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;">合作协议书</p><p style="color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">合同编号：HT20230712</p><div style="color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">fasdfasdfasdfasdfa</div><div><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">甲方：</p><img src="未标题-2.jpg" alt="甲方印章" style="width: 150px; height: 150px; margin-top: 22px;"><div style="margin-top: 22px; display: flex;">甲方公司：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">aaa</div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div><div style="margin-top: 32px;"><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">已方：</p><div style="margin-top: 22px; display: flex;">手写签名：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;"><img src="未标题-2.jpg" style="height: 35px;"></div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div></body></html>';
        $htmlContentHeader = '<!DOCTYPE html><html lang=\'zh-CN\'><head><meta charset=\'UTF-8\'><title>合同页面</title></head><body style=\'padding: 20px 32px 20px 32px; min-width: 311px;\'><p style=\'overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;\'>合作协议书</p><p style=\'color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>合同编号：HT20230712</p><div style=\'color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>'.$content.'</div><div><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>甲方：</p><img src=\'未标题-2.jpg\' alt=\'甲方印章\' style=\'width: 150px; height: 150px; margin-top: 22px;\'><div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div><div style=\'margin-top: 32px;\'><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>已方：</p>';
        $htmlContentOptions = '';
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $htmlContentFoot = '<div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div></body></html>';
        if (1 == $sign['handSignature']) {
            $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>手写签名：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'></div></div>';
        }
        $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>乙方姓名：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$userProfile['truename'].'</div></div>';
        if (1 == $sign['IDNumber']) {
            $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>身份证号：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$userProfile['idCard'].'</div></div>';
        }
        if (1 == $sign['phoneNumber']) {
            $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>联系方式：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$user['verifiedMobile'] ? $user['verifiedMobile'] : ''.'</div></div>';
        }

        return $htmlContentHeader.$htmlContentOptions.$htmlContentFoot;
    }

    protected function getHtmlByRecord($content, $sign)
    {
//        return $htmlContent = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>合同页面</title></head><body style="padding: 20px 32px 20px 32px; min-width: 311px;"><p style="overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;">合作协议书</p><p style="color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">合同编号：HT20230712</p><div style="color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">fasdfasdfasdfasdfa</div><div><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">甲方：</p><img src="未标题-2.jpg" alt="甲方印章" style="width: 150px; height: 150px; margin-top: 22px;"><div style="margin-top: 22px; display: flex;">甲方公司：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">aaa</div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div><div style="margin-top: 32px;"><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">已方：</p><div style="margin-top: 22px; display: flex;">手写签名：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;"><img src="未标题-2.jpg" style="height: 35px;"></div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div></body></html>';
        $htmlContentHeader = '<!DOCTYPE html><html lang=\'zh-CN\'><head><meta charset=\'UTF-8\'><title>合同页面</title></head><body style=\'padding: 20px 32px 20px 32px; min-width: 311px;\'><p style=\'overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;\'>合作协议书</p><p style=\'color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>合同编号：HT20230712</p><div style=\'color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>'.$content.'</div><div><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>甲方：</p><img src=\'未标题-2.jpg\' alt=\'甲方印章\' style=\'width: 150px; height: 150px; margin-top: 22px;\'><div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div><div style=\'margin-top: 32px;\'><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>已方：</p>';
        $htmlContentOptions = '';
        $htmlContentFoot = '<div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div></body></html>';
        if (!empty($sign['handSignature'])) {
            $htmlContentFoot += '<div style=\'margin-top: 22px; display: flex;\'>手写签名：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$sign['handSignature'] ? $sign['handSignature'] : ''.'</div></div>';
        }
        $htmlContentFoot += '<div style=\'margin-top: 22px; display: flex;\'>乙方姓名：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$sign['truename'] ? $sign['truename'] : ''.'</div></div>';
        if (!empty($sign['IDNumber'])) {
            $htmlContentFoot += '<div style=\'margin-top: 22px; display: flex;\'>身份证号：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$sign['IDNumber'] ? $sign['IDNumber'] : ''.'</div></div>';
        }
        if (!empty($sign['phoneNumber'])) {
            $htmlContentFoot += '<div style=\'margin-top: 22px; display: flex;\'>联系方式：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$sign['phoneNumber'] ? $sign['phoneNumber'] : ''.'</div></div>';
        }

        return $htmlContentHeader + $htmlContentOptions + $htmlContentFoot;
    }

    /**
     * 根据输入的字符串拆分并获取对应的服务
     *
     * @param string $input
     *
     * @return mixed
     */
    private function getServiceByType($type)
    {
        switch ($type) {
            case 'course':
                $serviceName = 'Course:CourseService';
                break;
            case 'classroom':
                $serviceName = 'Classroom:ClassroomService';
                break;
            case 'itemBank':
                $serviceName = 'ItemBank:ItemBank:ItemBankService';
                break;
            default:
                throw new \Exception('Unknown type: '.$type);
        }

        return $this->createService($serviceName);
    }

    private function getMemberService($type)
    {
        switch ($type) {
            case 'course':
                $serviceName = 'Course:MemberService';
                break;
            case 'classroom':
                $serviceName = 'Classroom:MemberService';
                break;
            case 'itemBankExercise':
                $serviceName = 'ItemBankExercise:ExerciseMemberService';
                break;
            default:
                throw new \Exception('Unknown type: '.$type);
        }

        return $this->createService($serviceName);
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
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

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
