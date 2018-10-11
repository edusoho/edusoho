<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\UserException;

class PageDiscovery extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $portal)
    {
        if (!in_array($portal, array('h5', 'miniprogram'))) {
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
                if ('condition' == $discoverySetting['data']['sourceType']) {
                    $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('creator', 'teacherIds'));
                    $this->getOCUtil()->multiple($discoverySetting['data']['items'], array('courseSetId'), 'courseSet');
                }
                $discoverySetting['data']['source'] = array(
                    'category' => $discoverySetting['data']['categoryId'],
                    'courseType' => 'all',
                    'sort' => $discoverySetting['data']['sort'],
                );
            }
        }

        return $discoverySettings;
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
