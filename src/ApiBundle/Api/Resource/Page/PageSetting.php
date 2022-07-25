<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use Biz\User\UserException;

class PageSetting extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $portal, $type)
    {
        $mode = $request->query->get('mode', 'published');

        if (!in_array($mode, ['draft', 'published'])) {
            throw PageException::ERROR_MODE();
        }
        $type = 'course' == $type ? 'courseCondition' : $type;
        if (!in_array($type, ['courseCondition', 'discovery'])) {
            throw PageException::ERROR_TYPE();
        }

        if (!in_array($portal, ['h5', 'miniprogram', 'apps'])) {
            throw PageException::ERROR_PORTAL();
        }
        $method = 'get'.ucfirst($type);

        return $this->$method($portal, $mode);
    }

    /**
     * @ApiConf(isRequiredAuth=true)
     */
    public function add(ApiRequest $request, $portal)
    {
        $mode = $request->query->get('mode');
        if (!in_array($mode, ['draft', 'published'])) {
            throw PageException::ERROR_MODE();
        }
        $type = $request->query->get('type');
        if (!in_array($type, ['discovery'])) {
            throw PageException::ERROR_TYPE();
        }

        if (!in_array($portal, ['h5', 'miniprogram', 'apps'])) {
            throw PageException::ERROR_PORTAL();
        }

        $this->checkPermissionByPortal($portal);
        $content = $request->request->all();
        $method = 'add'.ucfirst($type);

        return $this->$method($portal, $mode, $content);
    }

    public function remove(ApiRequest $request, $portal, $type)
    {
        $mode = $request->query->get('mode');
        if ('draft' != $mode) {
            throw PageException::ERROR_MODE();
        }
        if (!in_array($type, ['discovery'])) {
            throw PageException::ERROR_TYPE();
        }

        if (!in_array($portal, ['h5', 'miniprogram', 'apps'])) {
            throw PageException::ERROR_PORTAL();
        }

        $this->checkPermissionByPortal($portal);
        $method = 'remove'.ucfirst($type);

        return $this->$method($portal, $mode);
    }

    private function checkPermissionByPortal($portal)
    {
        if ('h5' === $portal && ($this->getCurrentUser()->hasPermission('admin_v2_h5_set') || $this->getCurrentUser()->hasPermission('admin_h5_set'))) {
            return true;
        }

        if ('miniprogram' === $portal && ($this->getCurrentUser()->hasPermission('admin_v2_wechat_app_manage') || $this->getCurrentUser()->hasPermission('admin_wechat_app_manage'))) {
            return true;
        }

        if ('apps' === $portal && ($this->getCurrentUser()->hasPermission('admin_v2_setting_mobile') || $this->getCurrentUser()->hasPermission('admin_setting_mobile'))) {
            return true;
        }

        throw UserException::PERMISSION_DENIED();
    }

    protected function addDiscovery($portal, $mode = 'draft', $content = [])
    {
        $this->getSettingService()->set("{$portal}_{$mode}_discovery", $this->paramFilter($content));

        return $this->getDiscovery($portal, $mode);
    }

    protected function removeDiscovery($portal, $mode = 'draft')
    {
        $this->getSettingService()->delete("{$portal}_{$mode}_discovery");

        return ['success' => true];
    }

    protected function getDiscovery($portal, $mode = 'published')
    {
        $user = $this->getCurrentUser();
        if ('draft' == $mode && !$user->isAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        $discoverySettings = $this->getH5SettingService()->getDiscovery($portal, $mode, 'setting');
        foreach ($discoverySettings as &$discoverySetting) {
            $discoverySetting = $this->handleSetting($discoverySetting);
        }

        return $discoverySettings;
    }

    protected function paramFilter($discoverySettings)
    {
        $discoverySettings = $this->getH5SettingService()->filter($discoverySettings, 'setting');
        foreach ($discoverySettings as &$discoverySetting) {
            $discoverySetting = $this->handleSetting($discoverySetting);
            unset($discoverySetting['tips']);
        }

        return $discoverySettings;
    }

    protected function handleSetting($discoverySetting)
    {
        if ('course_list' == $discoverySetting['type']) {
            $this->getOCUtil()->multiple($discoverySetting['data']['items'], ['creator', 'teacherIds']);
            $this->getOCUtil()->multiple($discoverySetting['data']['items'], ['courseSetId'], 'courseSet');
        }
        if ('classroom_list' == $discoverySetting['type']) {
            $this->getOCUtil()->multiple($discoverySetting['data']['items'], ['creator', 'teacherIds', 'assistantIds', 'headTeacherId']);
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

    protected function getCourseCondition($portal, $mode = 'published')
    {
        return $this->getH5SettingService()->getCourseCondition($portal, $mode);
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
