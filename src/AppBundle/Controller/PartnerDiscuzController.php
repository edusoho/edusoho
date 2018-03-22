<?php

namespace AppBundle\Controller;

use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PartnerDiscuzController extends BaseController
{
    public function notifyAction(Request $request)
    {
        $this->initUcenter();

        $_DCACHE = $get = $post = array();
        $code = @$_GET['code'];
        parse_str(uc_authcode($code, 'DECODE', UC_KEY), $get);
        if (MAGIC_QUOTES_GPC) {
            $get = $this->stripslashes($get);
        }

        $timestamp = time();
        if ($timestamp - $get['time'] > 3600) {
            return new Response('Authracation has expiried');
        }
        if (empty($get)) {
            return new Response('Invalid Request');
        }
        // $action = $get['action'];

        $this->requireClientFile('lib/xml.class.php');

        $xml = file_get_contents('php://input');
        $post = xml_unserialize($xml);

        if (!in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
            return new Response(API_RETURN_FAILED);
        }

        $method = 'do'.ucfirst($get['action']);
        $result = $this->$method($request, $get, $post);

        return new Response($result);
    }

    protected function doTest($request, $get, $post)
    {
        return API_RETURN_SUCCEED;
    }

    protected function doDeleteuser($request, $get, $post)
    {
        $uids = $get['ids'];
        !API_DELETEUSER && exit(API_RETURN_FORBIDDEN);

        return API_RETURN_SUCCEED;
    }

    protected function doRenameuser($request, $get, $post)
    {
        if (UC_CHARSET == 'gbk') {
            $get['newusername'] = iconv('gb2312', 'UTF-8', $get['newusername']);
        }

        $bindUser = $this->getUserService()->getUserBindByTypeAndFromId('discuz', $get['uid']);
        $user = $this->getUserService()->getUser($bindUser['toId']);
        $this->getUserService()->changeNickname($user['id'], $get['newusername']);

        return API_RETURN_SUCCEED;
    }

    protected function doGettag($request, $get, $post)
    {
        return API_RETURN_SUCCEED;
    }

    protected function doSynlogin($request, $get, $post)
    {
        if (!API_SYNLOGIN) {
            return API_RETURN_FORBIDDEN;
        }

        $partnerUser = uc_get_user($get['uid'], 1);

        $bind = $this->getUserService()->getUserBindByTypeAndFromId('discuz', $get['uid']);

        if (UC_CHARSET == 'gbk') {
            $get['username'] = iconv('gb2312', 'UTF-8', $get['username']);
        }

        if (empty($bind)) {
            $registration = array(
                'nickname' => $get['username'],
                'email' => $partnerUser[2],
                'password' => substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 8),
                'createdTime' => $get['time'],
                'createdIp' => $request->getClientIp(),
                'token' => array('userId' => $get['uid']),
                'type' => 'discuz',
            );

            if (!$this->getAuthService()->isRegisterEnabled()) {
                return API_RETURN_FORBIDDEN;
            }

            $user = $this->getUserService()->register(
                $registration,
                $this->getRegisterTypeToolkit()->getRegisterTypes($registration)
            );
        } else {
            $user = $this->getUserService()->getUser($bind['toId']);
            if (empty($user)) {
                return API_RETURN_SUCCEED;
            }
        }

        $this->authenticateUser($user);

        return API_RETURN_SUCCEED;
    }

    protected function doSynlogout($request, $get, $post)
    {
        if (!API_SYNLOGOUT) {
            return API_RETURN_FORBIDDEN;
        }
        $this->get('security.token_storage')->setToken(null);
        $this->get('request')->getSession()->invalidate();

        return API_RETURN_SUCCEED;
    }

    protected function doUpdatepw($request, $get, $post)
    {
        return API_RETURN_SUCCEED;
    }

    protected function doUpdatebadwords($request, $get, $post)
    {
        if (!API_UPDATEBADWORDS) {
            return API_RETURN_FORBIDDEN;
        }
        $data = array();
        if (is_array($post)) {
            foreach ($post as $k => $v) {
                $data['findpattern'][$k] = $v['findpattern'];
                $data['replace'][$k] = $v['replacement'];
            }
        }
        $content = "<?php\r\n";
        $content .= '$_CACHE[\'badwords\'] = '.var_export($data, true).";\r\n";
        $this->writeCacheFile('badwords.php', $content);

        return API_RETURN_SUCCEED;
    }

    protected function doUpdatehosts($request, $get, $post)
    {
        if (!API_UPDATEHOSTS) {
            return API_RETURN_FORBIDDEN;
        }
        $content = "<?php\r\n";
        $content .= '$_CACHE[\'hosts\'] = '.var_export($post, true).";\r\n";
        $this->writeCacheFile('hosts.php', $content);

        return API_RETURN_SUCCEED;
    }

    protected function doUpdateapps($request, $get, $post)
    {
        if (!API_UPDATEAPPS) {
            return API_RETURN_FORBIDDEN;
        }
        $UC_API = $post['UC_API'];

        //note 写 app 缓存文件
        $content = "<?php\r\n";
        $content .= '$_CACHE[\'apps\'] = '.var_export($post, true).";\r\n";
        $this->writeCacheFile('apps.php', $content);

        $clientDirectory = $this->container->getParameter('kernel.root_dir').'/config/';

        //note 写配置文件

        return API_RETURN_SUCCEED;
    }

    protected function doUpdateclient($request, $get, $post)
    {
        if (!API_UPDATECLIENT) {
            return API_RETURN_FORBIDDEN;
        }

        $content = "<?php\r\n";
        $content .= '$_CACHE[\'settings\'] = '.var_export($post, true).";\r\n";
        $this->writeCacheFile('settings.php', $content);

        return API_RETURN_SUCCEED;
    }

    protected function doUpdatecredit($request, $get, $post)
    {
        return API_RETURN_SUCCEED;
    }

    protected function doGetcreditsettings($request, $get, $post)
    {
        return API_RETURN_SUCCEED;
    }

    protected function doUpdatecreditsettings($request, $get, $post)
    {
        return API_RETURN_SUCCEED;
    }

    protected function initUcenter()
    {
        define('UC_CLIENT_VERSION', '1.6.0');
        define('UC_CLIENT_RELEASE', '20110501');

        define('API_DELETEUSER', 1);
        define('API_RENAMEUSER', 1);
        define('API_GETTAG', 1);
        define('API_SYNLOGIN', 1);
        define('API_SYNLOGOUT', 1);
        define('API_UPDATEPW', 1);
        define('API_UPDATEBADWORDS', 1);
        define('API_UPDATEHOSTS', 1);
        define('API_UPDATEAPPS', 1);
        define('API_UPDATECLIENT', 1);
        define('API_UPDATECREDIT', 1);
        define('API_GETCREDIT', 1);
        define('API_GETCREDITSETTINGS', 1);
        define('API_UPDATECREDITSETTINGS', 1);
        define('API_ADDFEED', 1);
        define('API_RETURN_SUCCEED', '1');
        define('API_RETURN_FAILED', '-1');
        define('API_RETURN_FORBIDDEN', '-2');

        //set_magic_quotes_runtime(0);

        defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

        $setting = $this->getSettingService()->get('user_partner');
        $discuzConfig = $setting['partner_config']['discuz'];

        foreach ($discuzConfig as $key => $value) {
            define(strtoupper($key), $value);
        }

        $this->requireClientFile('client.php');
    }

    protected function requireClientFile($path)
    {
        $clientDirectory = realpath($this->container->getParameter('kernel.root_dir').'/../vendor_user/uc_client');
        require_once $clientDirectory.'/'.$path;
    }

    protected function writeCacheFile($filename, $content)
    {
        $cacheDirectory = $this->container->getParameter('kernel.root_dir').'/data/discuz/';

        if (!is_dir($cacheDirectory)) {
            mkdir($cacheDirectory);
        }

        $cachefile = $cacheDirectory.$filename;
        $fp = fopen($cachefile, 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

    protected function stripslashes($string)
    {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = $this->stripslashes($val);
            }
        } else {
            $string = stripslashes($string);
        }

        return $string;
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getRegisterTypeToolkit()
    {
        $biz = $this->getBiz();

        return $biz['user.register.type.toolkit'];
    }
}
