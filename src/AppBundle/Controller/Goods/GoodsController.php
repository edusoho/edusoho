<?php

namespace AppBundle\Controller\Goods;

use ApiBundle\Api\ApiRequest;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Goods\Service\GoodsService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserFieldService;
use DrpPlugin\Biz\AgencyBindRelation\Service\RelationService;
use Symfony\Component\HttpFoundation\Request;

class GoodsController extends BaseController
{
    private $yxDomain = 'http://yx.qiqiuyun.net';

    public function showAction(Request $request, $id)
    {
        $goodsApiRequest = new ApiRequest("/api/goods/{$id}", 'GET', ['preview' => $request->query->get('preview', 0)]);
        $goods = $this->container->get('api_resource_kernel')->handleApiRequest($goodsApiRequest);
        $goodsComponentsApiRequest = new ApiRequest("/api/goods/{$id}/components", 'GET');
        $goodsComponents = $this->container->get('api_resource_kernel')->handleApiRequest($goodsComponentsApiRequest);

        return $this->render(
            'goods/show.html.twig',
            [
                'goods' => $goods,
                'goodsComponents' => $goodsComponents,
                'drpInfo' => $this->get('kernel')->getPluginConfigurationManager()->isPluginInstalled('Drp') ? $this->getDrpInfo($goods) : [],
            ]
        );
    }

    protected function getDrpInfo($goods)
    {
        $type = $goods['product']['targetType'];
        $targetId = $goods['product']['id'];
        $drpSettings = $this->getSettingService()->get('drp', []);

        $recruitUrl = $this->yxDomain.'/distributioncenter.html#/agencyshow?merchant_id='.$drpSettings['merchantId'].'&type='.$type.'&id='.$targetId;
        $generalizeUrl = $this->yxDomain.'/distributioncenter.html#/template/share?type='.$type.'&id='.$targetId.'&merchant_id='.$drpSettings['merchantId'];

        $tagVisible = $this->getVisibleStatus();

        $bindRelation = $this->tryRefreshAgencyEquityReward($tagVisible);

        if (empty($bindRelation)) {
            $directRewardRatio = $drpSettings['minDirectRewardRatio'];
        } else {
            $directRewardRatio = $bindRelation['directRewardRatio'];
        }
        if ($type == 'course') {
            $product = $this->getCourseService()->getCourse($targetId);
        } else {
            $product = $this->getClassroomService()->getClassroom($targetId);
        }
        $earnings = number_format($product['price'] * $directRewardRatio, 2);

        return [
            'earnings' => $earnings,
            'bindRelation' => $bindRelation,
            'type' => $type,
            'id' => $targetId,
            'tagVisible' => $earnings > 0 ? $tagVisible : false,
            'recruitUrl' => $recruitUrl,
            'generalizeUrl' => $generalizeUrl,
            'isMobile' => $this->isMobileClient(),
        ];
    }

    protected function getVisibleStatus()
    {
        $drpSettings = $this->getSettingService()->get('drp', []);
        if (empty($drpSettings) || empty($drpSettings['merchantId']) || empty($drpSettings['refreshTime']) || empty($drpSettings['serviceStatus']) || ($drpSettings['serviceStatus'] != 'enable')) {
            return false;
        }

        return true;
    }

    protected function tryRefreshAgencyEquityReward($tagVisible)
    {
        $userId = $this->getUser()->getId();
        $agencyBindRelation = $this->getAgencyBindRelationService()->getRelationByUserId($userId);
        if (!$tagVisible || empty($agencyBindRelation)) {
            return $agencyBindRelation;
        }
        if (time() - $agencyBindRelation['updatedTime'] < 60 * 60 * 3) {
            return $agencyBindRelation;
        }
        $data = array(
            'merchantId' => $agencyBindRelation['merchantId'],
            'agencyUserId' => $agencyBindRelation['agencyId'],
        );

        $result = $this->getDrpPlatformApi()->getAgencyRewardRatio($data);

        if (!empty($result) && empty($result['error']) && isset($result['direct_reward_ratio'])) {
            $agencyBindRelation = $this->getAgencyBindRelationService()->updateRelation($agencyBindRelation['id'], array('directRewardRatio' => $result['direct_reward_ratio']));
        } else {
            throw new \ErrorException(json_encode($result));
        }

        return $agencyBindRelation;
    }

    public function buyFLowModalAction(Request $request)
    {
        if (!in_array($request->query->get('template'), ['no-remain', 'payments-disabled', 'avatar-alert', 'fill-user-info'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $params = [];

        if ('fill-user-info' === $request->query->get('template')) {
            $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
            $user = $this->getCurrentUser();
            $userInfo = $this->getUserService()->getUserProfile($user['id']);
            $userInfo['approvalStatus'] = $user['approvalStatus'];

            $params['userFields'] = $userFields;
            $params['user'] = $userInfo;
        }

        return $this->render(
            'buy-flow/'.$request->query->get('template').'-modal.html.twig', $params);
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return \DrpPlugin\Biz\Drp\Api\DrpPlatformApi
     */
    protected function  getDrpPlatformApi()
    {
        return $this->getBiz()->offsetGet('drp.plugin.platform_api');
    }

    /**
     * @return RelationService
     */
    protected function getAgencyBindRelationService()
    {
        return $this->createService('DrpPlugin:AgencyBindRelation:RelationService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
