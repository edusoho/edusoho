<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Resource\AbstractResource;

class PageQrcode extends AbstractResource
{
    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function get(ApiRequest $request, $portal, $type)
    {
        $user = $this->getCurrentUser();
        $url = $this->generateUrl('homepage', array(), true);

        $token = $this->getTokenService()->makeToken(
            'qrcode',
            array(
                'userId' => $user['id'],
                'data' => array(
                    'url' => $url,
                ),
                'times' => 1,
                'duration' => 1800,
            )
        );
        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        return array(
            'img' => $this->generateUrl('common_qrcode', array('text' => $url), true),
        );
    }

    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}
