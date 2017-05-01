<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\BannedCredentialException;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\EncryptionToolkit;
use AppBundle\Component\DeviceDetector\DeviceDetectorAdapter;
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
            'os' => $this->getOs($request)
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
                $user['vip'] = null;
            }

        }

        return $user;
    }

    private function getOs(ApiRequest $request)
    {
        $detector = new DeviceDetectorAdapter($request->headers->get('User-Agent'));
        $os = $detector->getOs();
        return $os ? $os['name'] : null;
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