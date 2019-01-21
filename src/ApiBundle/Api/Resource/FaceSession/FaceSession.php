<?php

namespace ApiBundle\Api\Resource\FaceSession;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\User\TokenException;
use Biz\User\UserException;
use Biz\Face\Service\FaceService;

class FaceSession extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $sessionId)
    {
        $session = $this->getFaceService()->getAiFaceSdk()->getFaceSession($sessionId);
        $loginToken = $request->query->get('loginToken');

        if (!empty($session['status']) && in_array($session['status'], array(FaceService::FACE_STATUS_FAIL, FaceService::FACE_STATUS_SUCCESS))) {
            $user = $this->getUserService()->getUser($session['user']['id']);
            if (empty($user)) {
                throw UserException::NOTFOUND_USER();
            }

            $log = array(
                'status' => $session['status'],
                'userId' => $user['id'],
                'sessionId' => $session['id'],
            );
            $this->getFaceService()->createFaceLog($log);

            if (FaceService::FACE_STATUS_SUCCESS == $session['status']) {
                $session['login'] = array(
                    'token' => $this->getUserService()->makeToken('mobile_login', $user['id'], time() + 3600 * 24 * 30),
                    'user' => $user,
                );

                if ('register' == $session['type'] && empty($user['faceRegistered'])) {
                    $this->getUserService()->setFaceRegistered($user['id']);
                }
            }

            if (FaceService::FACE_STATUS_FAIL == $session['status']) {
                $conditions = array(
                    'userId' => $session['user']['id'],
                    'createdTime_GT' => time() - FaceService::FACE_FIAL_TIME_INTERVAL,
                    'status' => FaceService::FACE_STATUS_FAIL,
                );
                $count = $this->getFaceService()->countFaceLog($conditions);

                if ($count >= FaceService::FACE_FIAL_TIMES) {
                    $session['lastFailed'] = 1;
                }
            }

            if (!empty($loginToken)) {
                $token = $this->getTokenService()->verifyToken('face_login', $loginToken, $session);
                if (!$token) {
                    throw TokenException::TOKEN_INVALID();
                }
            }
        }

        return $session;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $loginToken = $request->request->get('loginToken');
        
        $user = $this->getCurrentUser();
        $type = $request->request->get('type', 'register');
        if ('register' == $type && !$user->isLogin()) {
            throw UserException::NOTFOUND_USER();
        }

        if ('compare' == $type) {
            $loginField = $request->request->get('loginField');
            if (empty($loginField)) {
                throw CommonException::ERROR_PARAMETER_MISSING();
            }
            $user = $this->getUserService()->getUserByLoginField($loginField);

            if (empty($user) || !$user['faceRegistered']) {
                throw UserException::NOTFOUND_USER();
            }
        }

        $session = $this->getFaceService()->getAiFaceSdk()->createFaceSession($user['id'], $user['nickname'], $type);

        if (!empty($loginToken)) {
            $token = $this->getTokenService()->verifyToken('face_login', $loginToken, $session);
            if (!$token) {
                throw TokenException::TOKEN_INVALID();
            }
        }

        $user = $this->getUserService()->getUser($session['user']['id']);
        $this->getLogService()->info('mobile', 'face_login', "{$user['nickname']}通过人脸识别登录", array('loginUser' => $user));

        return $session;
    }

    /**
     * @return \Biz\System\Service\Impl\LogServiceImpl
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
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
