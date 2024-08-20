<?php

namespace ApiBundle\Api\Resource\Contract;

use ApiBundle\Api\Util\AssetHelper;
use Biz\Classroom\Service\ClassroomService;
use Biz\Contract\Service\ContractService;
use Biz\Course\Service\CourseService;
use Biz\User\Service\UserService;

trait ContractDisplayTrait
{
    private function getGoodsName($goodsKey)
    {
        list($goodsType, $targetId) = $this->parseGoodsKey($goodsKey);
        if ('course' == $goodsType) {
            $course = $this->getCourseService()->getCourse($targetId);

            return "{$course['courseSetTitle']}-{$course['title']}";
        }
        if ('classroom' == $goodsType) {
            $classroom = $this->getClassroomService()->getClassroom($targetId);

            return $classroom['title'];
        }
    }

    private function getContractDetail($contract, $goodsKey)
    {
        $code = $this->getContractService()->generateContractCode();
        $content = $this->getDetailContent($contract['content'], $goodsKey, $code);

        return $this->getHtml($content, $contract, $code);
    }

    private function getDetailContent($content, $goodsKey, $contractCode)
    {
        $parts = explode('_', $goodsKey);
        $product = [];
        if ('course' == $parts[0]) {
            $product = $this->getServiceByType($parts[0])->getCourse($parts[1]);
            $product['title'] = $product['courseSetTitle'].'-'.$product['title'];
        } elseif ('classroom' == $parts[0]) {
            $product = $this->getServiceByType($parts[0])->getClassroom($parts[1]);
        } elseif ('itemBankExercise' == $parts[0]) {
            $product = $this->getServiceByType($parts[0])->getItemBank($parts[1]);
        }
        $member = [];
        if ('course' == $parts[0]) {
            $member = $this->getMemberService($parts[0])->getCourseMember($parts[1], $this->getCurrentUser()->getId());
        } elseif ('classroom' == $parts[0]) {
            $member = $this->getMemberService($parts[0])->getClassroomMember($parts[1], $this->getCurrentUser()->getId());
        } elseif ('itemBankExercise' == $parts[0]) {
            $member = $this->getMemberService($parts[0])->getExerciseMember($parts[1], $this->getCurrentUser()->getId());
        }
        $order = $this->getOrderService()->getOrder($member['orderId']);
        $user = $this->getCurrentUser();
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $content = str_replace("\n", "<br>", $content);
        return str_replace(
            ['$name$', '$username$', '$idcard$', '$courseName$', '$contract number$', '$date$', '$order price$'],
            [$userProfile['truename'] ?? '', $user['nickname'] ?? '', $userProfile['idcard'] ?? '', $product['title'] ?? '', $contractCode, date('Y年m月d日') ?? '', $order['pay_amount'] ?? ''],
            $content
        );
    }

    protected function getHtml($content, $contract, $code)
    {
//        return $htmlContent = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>合同页面</title></head><body style="padding: 20px 32px 20px 32px; min-width: 311px;"><p style="overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;">合作协议书</p><p style="color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">合同编号：HT20230712</p><div style="color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">fasdfasdfasdfasdfa</div><div><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">甲方：</p><img src="未标题-2.jpg" alt="甲方印章" style="width: 150px; height: 150px; margin-top: 22px;"><div style="margin-top: 22px; display: flex;">甲方公司：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">aaa</div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div><div style="margin-top: 32px;"><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">已方：</p><div style="margin-top: 22px; display: flex;">手写签名：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;"><img src="未标题-2.jpg" style="height: 35px;"></div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div></body></html>';
        $htmlContentHeader = '<html lang=\'zh-CN\'><head><meta charset=\'UTF-8\'><title>合同页面</title></head><body><p style=\'overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;\'>'.$contract['name'].'</p><p style=\'color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>合同编号：'.$code.'</p><div style=\'color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>'.$content.'</div><div><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>甲方：</p><img src=\''.AssetHelper::getFurl($contract['seal']).'\' alt=\'甲方印章\' style=\'width: 150px; height: 150px; margin-top: 22px;\'><div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div><div style=\'margin-top: 32px;\'><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>乙方：</p>';
        $htmlContentOptions = '';
        $user = $this->getCurrentUser();
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $htmlContentFoot = '<div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div></body></html>';
        $sign = $contract['sign'];
        if (1 == $sign['handSignature']) {
            $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>手写签名：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'></div></div>';
        }
        $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>乙方姓名：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$userProfile['truename'].'</div></div>';
        if (1 == $sign['IDNumber']) {
            $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>身份证号：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$userProfile['idCard'].'</div></div>';
        }
        if (1 == $sign['phoneNumber']) {
            $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>联系方式：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$user['verifiedMobile'].'</div></div>';
        }

        return $htmlContentHeader.$htmlContentOptions.$htmlContentFoot;
    }

    private function getHtmlByRecord($content, $signSnapshot)
    {
//        return $htmlContent = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>合同页面</title></head><body style="padding: 20px 32px 20px 32px; min-width: 311px;"><p style="overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;">合作协议书</p><p style="color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">合同编号：HT20230712</p><div style="color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;">fasdfasdfasdfasdfa</div><div><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">甲方：</p><img src="未标题-2.jpg" alt="甲方印章" style="width: 150px; height: 150px; margin-top: 22px;"><div style="margin-top: 22px; display: flex;">甲方公司：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">aaa</div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div><div style="margin-top: 32px;"><p style="color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;">已方：</p><div style="margin-top: 22px; display: flex;">手写签名：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;"><img src="未标题-2.jpg" style="height: 35px;"></div></div><div style="margin-top: 22px; display: flex;">签约日期：<div style="display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;">2024年07月29日</div></div></div></body></html>';
        $htmlContentHeader = '<html lang=\'zh-CN\'><head><meta charset=\'UTF-8\'><title>合同页面</title></head><body><p style=\'overflow: hidden; color: #1E2226; text-overflow: ellipsis; font-size: 14px; font-style: normal; font-weight: 500; line-height: 22px; text-align: center;\'>'.$signSnapshot['contract']['name'].'</p><p style=\'color: #626973; text-align: right; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>合同编号：'.$signSnapshot['contractCode'].'</p><div style=\'color: #626973; font-family: \'PingFang SC\'; font-size: 12px; font-style: normal; font-weight: 400; line-height: 20px;\'>'.$content.'</div><div><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>甲方：</p><img src=\''.AssetHelper::getFurl($signSnapshot['contract']['seal']).'\' alt=\'甲方印章\' style=\'width: 150px; height: 150px; margin-top: 22px;\'><div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div><div style=\'margin-top: 32px;\'><p style=\'color: #1E2226; font-family: \'PingFang SC\'; font-size: 18px; font-style: normal; font-weight: 500; line-height: 26px;\'>乙方：</p>';
        $htmlContentOptions = '';
        $htmlContentFoot = '<div style=\'margin-top: 22px; display: flex;\'>签约日期：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.date('Y年m月d日').'</div></div></div></body></html>';
        $sign = $signSnapshot['sign'];
        if (!empty($sign['handSignature'])) {
            $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>手写签名：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'><img src=\''.$sign['handSignature'].'\' alt=\'手写签名\' style=\'height: 35px;\'></div></div>';
        }
        $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>乙方姓名：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$sign['truename'].'</div></div>';
        if (!empty($sign['IDNumber'])) {
            $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>身份证号：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$sign['IDNumber'].'</div></div>';
        }
        if (!empty($sign['phoneNumber'])) {
            $htmlContentOptions = $htmlContentOptions.'<div style=\'margin-top: 22px; display: flex;\'>联系方式：<div style=\'display: flex; align-items: center; gap: 10px; flex: 1 0 0; border-bottom: 0.5px solid #919399; width: 241px;\'>'.$sign['phoneNumber'].'</div></div>';
        }

        return $htmlContentHeader.$htmlContentOptions.$htmlContentFoot;
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

    private function parseGoodsKey($goodsKey)
    {
        return explode('_', $goodsKey);
    }

    /**
     * @return ContractService
     */
    private function getContractService()
    {
        return $this->service('Contract:ContractService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->service('Order:OrderService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
