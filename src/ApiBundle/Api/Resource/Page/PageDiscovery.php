<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Coupon\Service\CouponBatchService;
use Biz\User\UserException;

class PageDiscovery extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $portal)
    {
        if (!in_array($portal, array('h5', 'miniprogram', 'apps'))) {
            throw PageException::ERROR_PORTAL();
        }
        $params = $request->query->all();
        $mode = 'published';
        if (!empty($params['preview'])) {
            $token = $this->getTokenService()->verifyToken('qrcode_url', $params['token']);
            if (empty($token)) {
                throw UserException::PERMISSION_DENIED();
            }
            $mode = 'draft';
        }
        $discoverySettings = $this->getH5SettingService()->getDiscovery($portal, $mode);
        foreach ($discoverySettings as &$discoverySetting) {
            if ('course_list' == $discoverySetting['type']) {
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('creator', 'teacherIds'));
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('courseSetId'), 'courseSet');
                $discoverySetting['data']['source'] = array(
                    'category' => $discoverySetting['data']['categoryId'],
                    'courseType' => 'all',
                    'sort' => $discoverySetting['data']['sort'],
                );
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

            if ('open_course_list' == $discoverySetting['type']) {
                $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('userId', 'teacherIds'));
            }
        }

        return !empty($params['format']) && 'list' == $params['format'] ? array_values($discoverySettings) : $discoverySettings;
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->service('Coupon:CouponBatchService');
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getH5SettingService()
    {
        return $this->service('System:H5SettingService');
    }

    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
