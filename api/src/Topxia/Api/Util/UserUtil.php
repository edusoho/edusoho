<?php

namespace Topxia\Api\Util;
use Topxia\Service\Common\ServiceKernel;

class UserUtil
{
    public function generateUser($type, $token, $oauthUser,$setData)
    {
        $registration = array();

        $randString = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $oauthUser['name'] = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-z0-9_.]+/u', '', $oauthUser['name']);
        $oauthUser['name'] = str_replace(array('-'), array('_'), $oauthUser['name']);

        if (empty($oauthUser['name'])) {
            $oauthUser['name'] = "{$type}" . substr($randString, 9, 3);
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
            $nicknames[] = $oauthUser['name'];
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8') . substr($randString, 0, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8') . substr($randString, 3, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8') . substr($randString, 6, 3);

            foreach ($nicknames as $name) {
                if (ServiceKernel::instance()->createService('User.UserService')->isNicknameAvaliable($name)) {
                    $registration['nickname'] = $name;
                    break;
                }
            }

            if (empty($registration['nickname'])) {
                return null;
            }

            $registration['email'] = 'u_' . substr($randString, 0, 12) . '@edusoho.net';
        }
        $registration['password'] = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 8);
        $registration['token'] = $token;
        $registration['createdIp'] = $oauthUser['createdIp'];

        if(ServiceKernel::instance()->createService('System.SettingService')->get("auth.register_mode", "email") == "email_or_mobile") {
            $registration['emailOrMobile'] = $registration['email'];
            unset($registration['email']);
        }

        $user = ServiceKernel::instance()->createService('User.AuthService')->register($registration, $type);
        return $user;
    }
}