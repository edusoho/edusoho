<?php

namespace ApiBundle\Api\Resource\UserProfile;

use ApiBundle\Api\Resource\Filter;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ConvertIpToolkit;
use Biz\InfoSecurity\Service\MobileMaskService;
use Topxia\Service\Common\ServiceKernel;

class UserProfileFilter extends Filter
{
    protected $simpleFields = ['user', 'profile', 'fields'];

    protected $userFields = [
        'id', 'nickname', 'email', 'loginTime', 'loginIp', 'createdTime', 'createdIp', 'roles', 'title', 'uuid'
    ];

    protected $mode = self::SIMPLE_MODE;

    protected function simpleFields(&$data)
    {
        $data['user'] = ArrayToolkit::parts($data['user'], $this->userFields);
        $data['user']['loginLocation'] = $this->convertIp($data['user']['loginIp']);
        $data['user']['createdLocation'] = $this->convertIp($data['user']['createdIp']);
        $data['profile']['encryptedMobile'] = empty($data['profile']['mobile']) ? '' : $this->getMobileMaskService()->encryptMobile($data['profile']['mobile']);
        $data['profile']['mobile'] = empty($data['profile']['mobile']) ? '' : $this->getMobileMaskService()->maskMobile($data['profile']['mobile']);
    }

    protected function publicFields(&$data)
    {
        $data['user'] = ArrayToolkit::parts($data['user'], $this->userFields);
        $data['user']['loginLocation'] = $this->convertIp($data['user']['loginIp']);
        $data['user']['createdLocation'] = $this->convertIp($data['user']['createdIp']);
    }

    protected function convertIp($ip)
    {
        $location = ConvertIpToolkit::convertIp($ip);

        if ('N/A' === $location) {
            return '未知区域';
        }

        return $location;
    }

    /**
     * @return MobileMaskService
     */
    protected function getMobileMaskService()
    {
        return ServiceKernel::instance()->createService('InfoSecurity:MobileMaskService');
    }
}
