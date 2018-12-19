<?php

namespace ApiBundle\Api\Resource\FaceSession;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\TokenException;

class FaceSessionFinishUploadResult extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request, $sessionId)
    {
        $loginToken = $request->request->get('loginToken');

        $result = $this->getFaceService()->getAiFaceSdk()->finishFaceUpload($sessionId, $request->request->get('response_code'), $request->request->get('response_body'));

        if (!empty($loginToken)) {
            $session = $this->getFaceService()->getAiFaceSdk()->getFaceSession($sessionId);
            $token = $this->getTokenService()->verifyToken('face_login', $loginToken, $session);

            if (!$token) {
                throw TokenException::TOKEN_INVALID();
            }
        }

        return $result;
    }

    protected function getFaceService()
    {
        return $this->service('Face:FaceService');
    }

    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}
