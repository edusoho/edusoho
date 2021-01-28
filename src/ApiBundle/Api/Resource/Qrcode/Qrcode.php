<?php

namespace ApiBundle\Api\Resource\Qrcode;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Qrcode extends AbstractResource
{
    public function get(ApiRequest $request, $route)
    {
        if (!in_array($route, array('homepage'))) {
            throw CommonException::ERROR_PARAMETER();
        }
        $params = $this->fillParams($request->query->all());
        $user = $this->getCurrentUser();
        $url = $this->generateUrl($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
        $token = $this->getTokenService()->makeToken(
            'qrcode',
            array(
                'userId' => 0,
                'data' => array(
                    'url' => $url,
                ),
                'times' => 1,
                'duration' => 3600,
            )
        );
        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), UrlGeneratorInterface::ABSOLUTE_URL);

        return array(
            'img' => $this->generateUrl('common_qrcode', array('text' => $url), UrlGeneratorInterface::ABSOLUTE_URL),
        );
    }

    public function fillParams($params)
    {
        if (empty($params['times']) && empty($params['duration'])) {
            return $params;
        }
        $token = $this->getTokenService()->makeToken(
            'qrcode_url',
            array(
                'userId' => 0,
                'data' => array(),
                'times' => empty($params['times']) ? 0 : $params['times'],
                'duration' => empty($params['duration']) ? 0 : $params['duration'],
            )
        );
        unset($params['times']);
        unset($params['duration']);
        $params['token'] = $token['token'];

        return $params;
    }

    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}
