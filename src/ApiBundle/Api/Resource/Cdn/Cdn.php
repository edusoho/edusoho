<?php

namespace ApiBundle\Api\Resource\Cdn;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class Cdn extends AbstractResource
{
    /**
     * @return array
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request)
    {
        $data = $request->request->all();
        $cdn = array(
            'enabled' => isset($data['enabled']) ? $data['enabled'] : '',
            'defaultUrl' => isset($data['default_url']) ? $data['default_url'] : '',
            'userUrl' => isset($data['user_url']) ? $data['user_url'] : '',
            'contentUrl' => isset($data['content_url']) ? $data['content_url'] : '',
        );

        $this->getSettingService()->set('cdn', $cdn);

        return array('code' => 'success', 'msg' => "设置cdn, enabled:{$cdn['enabled']}, defaultUrl:{$cdn['defaultUrl']},userUrl:{$cdn['userUrl']}, contentUrl:{$cdn['contentUrl']}");
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
