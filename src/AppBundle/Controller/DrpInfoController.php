<?php

namespace AppBundle\Controller;

use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use DrpPlugin\Biz\AgencyBindRelation\Service\RelationService;
use Symfony\Component\HttpFoundation\Request;

class DrpInfoController extends BaseController
{
    private $yxDomain = 'http://yx.qiqiuyun.net';

    public function getDrpInfoAction(Request $request, $targetId, $type)
    {
        if (!$this->get('kernel')->getPluginConfigurationManager()->isPluginInstalled('Drp')) {
            return $this->createJsonResponse([]);
        }

        $drpSettings = $this->getSettingService()->get('drp', []);

        $recruitUrl = $this->yxDomain.'/distributioncenter.html#/agencyshow?merchant_id='.$drpSettings['merchantId'].'&type='.$type.'&id='.$targetId;
        $generalizeUrl = $this->yxDomain.'/distributioncenter.html#/template/share?type='.$type.'&id='.$targetId.'&merchant_id='.$drpSettings['merchantId'];

        $tagVisible = $this->getVisibleStatus();

        $this->tryRefreshAgencyRecruitSetting();

        $bindRelation = $this->tryRefreshAgencyEquityReward($tagVisible);

        if (empty($bindRelation)) {
            $directRewardRatio = $drpSettings['minDirectRewardRatio'];
        } else {
            $directRewardRatio = $bindRelation['directRewardRatio'];
        }
        if ('course' == $type) {
            $product = $this->getCourseService()->getCourse($targetId);
        } else {
            $product = $this->getClassroomService()->getClassroom($targetId);
        }
        $earnings = number_format($product['price'] * $directRewardRatio, 2);

        return $this->createJsonResponse([
            'earnings' => $earnings,
            'bindRelation' => $bindRelation,
            'type' => $type,
            'id' => $targetId,
            'tagVisible' => $earnings > 0 ? $tagVisible : false,
            'recruitUrl' => $recruitUrl,
            'generalizeUrl' => $generalizeUrl,
            'isMobile' => $this->isMobileClient(),
        ]);
    }

    protected function getVisibleStatus()
    {
        $drpSettings = $this->getSettingService()->get('drp', []);
        if (empty($drpSettings) || empty($drpSettings['merchantId']) || empty($drpSettings['refreshTime']) || empty($drpSettings['serviceStatus']) || ('enable' != $drpSettings['serviceStatus'])) {
            return false;
        }

        return true;
    }

    protected function tryRefreshAgencyRecruitSetting()
    {
        $drpSettings = $this->getSettingService()->get('drp', []);
        if (empty($drpSettings['serviceStatus']) || 'enable' != $drpSettings['serviceStatus']) {
            return true;
        }
        $refreshTime = empty($drpSettings['refreshTime']) ? 0 : $drpSettings['refreshTime'];
        // 定义3小时刷新一次网校权益最低比例
        if (!empty($drpSettings['merchantId']) && (time() - $refreshTime < 60 * 60 * 3)) {
            return true;
        }
        $storageSetting = $this->getSettingService()->get('storage', []);
        if (empty($storageSetting['cloud_access_key']) || empty($storageSetting['cloud_secret_key'])) {
            return true;
        }
        $result = $this->getDrpPlatformApi()->getAgencyRecruitBaseSetting(['accessKey' => $storageSetting['cloud_access_key']]);
        if (!empty($result) && empty($result['error']) && isset($result['minDirectRewardRatio'])) {
            $fields = [
                'merchantId' => $result['merchantId'],
                'recruitSwitch' => empty($result['isOpening']) ? 0 : $result['isOpening'],
                'minDirectRewardRatio' => $result['minDirectRewardRatio'],
                'serviceStatus' => $result['serviceStatus'],
                'refreshTime' => time(),
            ];
            if ($drpSettings['merchantId'] > 0) {
                unset($fields['merchantId']);
            }
            $newDrpSettings = array_merge($drpSettings, $fields);

            $this->getSettingService()->set('drp', $newDrpSettings);
        }
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
        $data = [
            'merchantId' => $agencyBindRelation['merchantId'],
            'agencyUserId' => $agencyBindRelation['agencyId'],
        ];

        $result = $this->getDrpPlatformApi()->getAgencyRewardRatio($data);

        if (!empty($result) && empty($result['error']) && isset($result['direct_reward_ratio'])) {
            $agencyBindRelation = $this->getAgencyBindRelationService()->updateRelation($agencyBindRelation['id'], ['directRewardRatio' => $result['direct_reward_ratio']]);
        } else {
            throw new \ErrorException(json_encode($result));
        }

        return $agencyBindRelation;
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
    protected function getDrpPlatformApi()
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
