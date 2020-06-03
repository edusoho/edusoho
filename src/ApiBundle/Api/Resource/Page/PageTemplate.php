<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use Biz\Coupon\Service\CouponBatchService;
use Biz\User\UserException;

class PageTemplate extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $portal, $template)
    {
        if (!in_array($portal, array('h5', 'miniprogram', 'apps'))) {
            throw PageException::ERROR_PORTAL();
        }

        $template = $this->getH5SettingService()->getDiscoveryTemplate($template, $portal, 'setting');
        foreach ($template as &$discoverySetting) {
            $discoverySetting = $this->handleSetting($discoverySetting);
        }

        return $template;
    }

    protected function handleSetting($discoverySetting)
    {
        if ('course_list' == $discoverySetting['type']) {
            $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('creator', 'teacherIds'));
            $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('courseSetId'), 'courseSet');
        }
        if ('classroom_list' == $discoverySetting['type']) {
            $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('creator', 'teacherIds', 'assistantIds', 'headTeacherId'));
        }
        if ('coupon' == $discoverySetting['type']) {
            foreach ($discoverySetting['data']['items'] as &$couponBatch) {
                $couponBatch['target'] = $this->getCouponBatchService()->getTargetByBatchId($couponBatch['id']);
                $couponBatch['targetDetail'] = $this->getCouponBatchService()->getCouponBatchTargetDetail($couponBatch['id']);
            }
        }
        $pageDiscoveryFilter = new PageDiscoveryFilter();
        $pageDiscoveryFilter->setMode(Filter::PUBLIC_MODE);
        $pageDiscoveryFilter->filter($discoverySetting);

        return $discoverySetting;
    }

    private function getCouponBatchService()
    {
        return $this->service('Coupon:CouponBatchService');
    }

    protected function getH5SettingService()
    {
        return $this->service('System:H5SettingService');
    }
}