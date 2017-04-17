<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\BannedCredentialException;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\BrowserDetectionUtil;
use AppBundle\Common\EncryptionToolkit;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;

class Token extends AbstractResource
{
    private $encryptionTypes = array('XXTEA');

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $username = $request->request->get('username');
        $password =  $request->request->get('password');
        $encryptionType = $request->request->get('encryptionType');

        if ($encryptionType ) {
            $password = $this->decryptPassword($password, $encryptionType);
        }

        $user = $this->checkParams($username, $password);

        $args = array(
            'userId' => $user['id'],
            'device' => $this->getDevice($request)
        );

        $token = $this->getTokenService()->makeApiAuthToken($args);

        $this->appendUser($user);

        return array(
            'token' => $token['token'],
            'user' => $user
        );
    }

    private function checkParams($username, $password)
    {
        $user = $this->getUserService()->getUserByLoginField($username);
        if (empty($user)) {
            throw new ResourceNotFoundException('用户帐号不存在');
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            throw new InvalidArgumentException('帐号密码不正确');
        }

        if ($user['locked']) {
            throw new BannedCredentialException('用户已锁定，请联系网校管理员');
        }

        return $user;
    }

    private function getDevice(ApiRequest $request)
    {
        $userAgent = $request->headers->get('User-Agent');
        preg_match("/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|
                    iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|
                    philips|phone|playbook|sagem|sharp|sie-|silk|smartphone|sony|symbian|t-mobile|telus|up\.browser|
                    up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i", $userAgent, $matches);

        if ($matches) {
            return current($matches);
        } else {
            $bdu = new BrowserDetectionUtil($userAgent);
            $bdu->detect();
            $browser = $bdu->getBrowser();

            return $browser ? $browser : TokenService::DEVICE_UNKNOWN;
        }
    }

    private function decryptPassword($password, $encryptionType)
    {
        if (!in_array($encryptionType, $this->encryptionTypes)) {
            throw new InvalidArgumentException('不正确的加密方式');
        }

        return EncryptionToolkit::XXTEADecrypt(base64_decode($password), 'edusoho');
    }

    private function appendUser(&$user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);

        if ($this->isPluginInstalled('vip')) {
            $vip = $this->service('VipPlugin:Vip:VipService')->getMemberByUserId($user['id']);
            $level = $this->service('VipPlugin:Vip:LevelService')->getLevel($vip['levelId']);

            if ($vip) {
                $user['vip'] = array(
                    'levelId' => $vip['levelId'],
                    'vipName' => $level['name'],
                    'deadline' => date('c', $vip['deadline']),
                    'seq' => $level['seq']
                );
            } else {
                $user['vip'] = array();
            }

        }

        return $user;
    }

    /**
     * @return TokenService
     */
    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}