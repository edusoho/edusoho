<?php

namespace Topxia\Api\Util;

use Biz\System\Service\SettingService;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\File\File;

class UserUtil
{
    public function generateUser($type, $token, $oauthUser, $setData)
    {
        $registration = array();

        $randString = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $oauthUser['name'] = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-z0-9_.]+/u', '', $oauthUser['name']);
        $oauthUser['name'] = str_replace(array('-'), array('_'), $oauthUser['name']);

        if (empty($oauthUser['name'])) {
            $oauthUser['name'] = "{$type}".substr($randString, 9, 3);
        }

        $nameLength = mb_strlen($oauthUser['name'], 'utf-8');
        if ($nameLength > 10) {
            $oauthUser['name'] = mb_substr($oauthUser['name'], 0, 11, 'utf-8');
        }

        if (!empty($setData['nickname']) && !empty($setData['email'])) {
            $registration['nickname'] = $setData['nickname'];
            $registration['email'] = $setData['email'];
        } else {
            $nicknames = array();
            if (mb_strlen($oauthUser['name'], 'utf-8') < 4) {
                $oauthUser['name'] .= substr($randString, 0, 3);
            }
            $nicknames[] = $oauthUser['name'];
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8').substr($randString, 0, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8').substr($randString, 3, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8').substr($randString, 6, 3);

            foreach ($nicknames as $name) {
                if (ServiceKernel::instance()->createService('User:UserService')->isNicknameAvaliable($name)) {
                    $registration['nickname'] = $name;
                    break;
                }
            }

            if (empty($registration['nickname'])) {
                return null;
            }

            $registration['email'] = 'u_'.substr($randString, 0, 12).'@edusoho.net';
        }
        $registration['password'] = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 8);
        $registration['token'] = $token;
        $registration['createdIp'] = $oauthUser['createdIp'];
        $registration['type'] = $type;
        $registration['authid'] = $oauthUser['id'];

        if ('email_or_mobile' == $this->getSettingService()->get('auth.register_mode', 'email')) {
            $registration['emailOrMobile'] = $registration['email'];
            unset($registration['email']);
        }

        $user = ServiceKernel::instance()->createService('User:AuthService')->register($registration, $type);

        return $user;
    }

    public function fillUserAttr($userId, $userInfo)
    {
        $user = ServiceKernel::instance()->createService('User:UserService')->getUser($userId);
        if (!empty($userInfo['avatar'])) {
            $curl = curl_init($userInfo['avatar']);

            $smallName = date('Ymdhis').'_small.jpg';
            $mediumName = date('Ymdhis').'_medium.jpg';
            $largeName = date('Ymdhis').'_large.jpg';

            $smallPath = ServiceKernel::instance()->getParameter('topxia.upload.public_directory').'/tmp/'.$smallName;
            $mediumPath = ServiceKernel::instance()->getParameter('topxia.upload.public_directory').'/tmp/'.$mediumName;
            $largePath = ServiceKernel::instance()->getParameter('topxia.upload.public_directory').'/tmp/'.$largeName;
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            $imageData = curl_exec($curl);
            curl_close($curl);
            $tp = @fopen($smallPath, 'a');
            fwrite($tp, $imageData);
            fclose($tp);
            $tp = @fopen($mediumPath, 'a');
            fwrite($tp, $imageData);
            fclose($tp);
            $tp = @fopen($largePath, 'a');
            fwrite($tp, $imageData);
            fclose($tp);

            $file = ServiceKernel::instance()->createService('Content:FileService')->uploadFile('user', new File($smallPath));
            $fields[] = array(
                'type' => 'large',
                'id' => $file['id'],
            );

            $file = ServiceKernel::instance()->createService('Content:FileService')->uploadFile('user', new File($mediumPath));
            $fields[] = array(
                'type' => 'medium',
                'id' => $file['id'],
            );

            $file = ServiceKernel::instance()->createService('Content:FileService')->uploadFile('user', new File($largePath));
            $fields[] = array(
                'type' => 'small',
                'id' => $file['id'],
            );
            $user = ServiceKernel::instance()->createService('User:UserService')->changeAvatar($userId, $fields);
        }

        return $user;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
