<?php

namespace ApiBundle\Api\Resource\Contract;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use Biz\Contract\Service\ContractService;
use Biz\User\Service\UserService;
use Codeages\Biz\Order\Service\OrderService;

class ContractSign extends AbstractResource
{
    public function get(ApiRequest $request, $contractId, $goodsKey)
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
        $contractGoodsRelation = $this->getContractService()->getContractGoodsRelationByContractId($contractId);
        $parts = explode('_', $contractGoodsRelation['goodsKey']);
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
        $contract['content'] = str_replace(
            ['$name$', '$username$', '$idcard$', '$courseName$', '$contract number$', '$date$', '$order price$'],
            [$userProfile['truename'] ?? '', $user['nickname'] ?? '', $userProfile['idcard'] ?? '', $product['title'] ?? '', $contract['id'] ?? '', date('Y年m月d日') ?? '', $order['pay_amount'] ?? ''],
            $contract['content']
        );

        $conditions = $request->query->all();
        if ($conditions['isMobile']) {
            $abc = $this->getHtml($contract['content'], null, AssetHelper::getFurl($contract['seal']));

            return ['abc' => $abc];
        }

        return [
            'id' => $contractId,
            'name' => $contract['name'],
            'code' => $this->getContractService()->generateContractCode(),
            'content' => $contract['content'],
            'seal' => AssetHelper::getFurl($contract['seal']),
            'signFields' => $signFields,
            'signDate' => date('Y年m月d日'),
        ];
    }

    protected function getHtml($content, $handSign, $seal)
    {
//        return $htmlContent = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>合同页面</title></head><body style="padding: 20px 32px 20px 32px; min-width: 311px;"><p style="overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;">合作协议书</p><p style="color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">合同编号：HT20230712</p><div style="color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">fasdfasdfasdfasdfa</div><div><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">甲方：</p><img src="未标题-2.jpg" alt="甲方印章" style="width: 150px; height: 150px; margin-top: 22px;"><div style="margin-top: 22px; display: flex;">甲方公司：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">aaa</div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div><div style="margin-top: 32px;"><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">已方：</p><div style="margin-top: 22px; display: flex;">手写签名：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;"><img src="未标题-2.jpg" style="height: 35px;"></div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div></body></html>';
        return $htmlContent = '<!DOCTYPE html><html lang=\'zh-CN\'><head><meta charset=\'UTF-8\'><title>合同页面</title></head><body style=\'padding: 20px 32px 20px 32px; min-width: 311px;\'><p style=\'overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;\'>合作协议书</p><p style=\'color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>合同编号：HT20230712</p><div style=\'color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>fasdfasdfasdfasdfa</div><div><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>甲方：</p><img src=\'未标题-2.jpg\' alt=\'甲方印章\' style=\'width: 150px; height: 150px; margin-top: 22px;\'><div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div><div style=\'margin-top: 32px;\'><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>已方：</p><div style=\'margin-top: 22px; display: flex;\'>手写签名：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'><img src=\'未标题-2.jpg\' style=\'height: 35px;\'></div></div><div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div></body></html>';
    }

    public function add(ApiRequest $request, $contractId)
    {
        $this->getContractService()->signContract($contractId, $request->request->all());

        return ['ok' => true];
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

        return $this->service($serviceName);
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

        return $this->service($serviceName);
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
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->service('Order:OrderService');
    }
}
