<?php

namespace ApiBundle\Api\Resource\App;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Util\EdusohoTuiClient;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AppPushToken extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $tuiClient = new EdusohoTuiClient();

        $token = $tuiClient->getToken();

        if (isset($token['error'])) {
            throw new BadRequestHttpException($token['error']);
        }

        return $token;
    }
}
