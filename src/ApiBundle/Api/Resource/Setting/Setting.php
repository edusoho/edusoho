<?php

namespace ApiBundle\Api\Resource\Setting;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Biz\System\SettingException;

class Setting extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $type)
    {
        if (!in_array($type, array('site', 'wap', 'register', 'payment', 'vip', 'magic', 'cdn', 'course', 'weixinConfig', 'login', 'face', 'miniprogram', 'hasPluginInstalled', 'classroom', 'wechat', 'cloud', 'user'))) {
            throw CommonException::ERROR_PARAMETER();
        }

        $method = "get${type}";

        return $this->$method($request);
    }

    public function getUser($request = null)
    {
        $authSetting = $this->getSettingService()->get('auth');
        $loginSetting = $this->getSettingService()->get('login_bind');

        if (empty($loginSetting)) {
            SettingException::NOTFOUND_THIRD_PARTY_AUTH_CONFIG();
        }

        $result = array(
            'auth' => array(
                'register_mode' => $authSetting['register_mode'],
                'user_terms_enabled' => 'opened' == $authSetting['user_terms'] ? true : false,
                'privacy_policy_enabled' => 'opened' == $authSetting['privacy_policy'] ? true : false,
            ),
            'login_bind' => array(
                'oauth_enabled' => (int) $loginSetting['enabled'] ? true : false,
                'weibo_enabled' => (int) $loginSetting['weibo_enabled'] ? true : false,
                'qq_enabled' => (int) $loginSetting['qq_enabled'] ? true : false,
                'weixinweb_enabled' => (int) $loginSetting['weixinweb_enabled'] ? true : false,
                'weixinmob_enabled' => (int) $loginSetting['weixinmob_enabled'] ? true : false,
            ),
        );

        return $result;
    }

    public function getCloud($request = null)
    {
        $cloudSms = $this->getSettingService()->get('cloud_sms');

        $result = array(
            'sms_enabled' => $cloudSms['sms_enabled'] ? true : false,
        );

        return $result;
    }

    public function getHasPluginInstalled($request)
    {
        $pluginCodes = $request->query->get('pluginCodes', '');
        if (empty($pluginCodes)) {
            throw CommonException::ERROR_PARAMETER();
        }
        if (!is_array($pluginCodes)) {
            $pluginCodes = explode(',', $pluginCodes);
        }

        $results = array();
        foreach ($pluginCodes as $pluginCode) {
            $results[$pluginCode] = $this->isPluginInstalled($pluginCode) ? true : false;
        }

        return $results;
    }

    public function getSite($request = null)
    {
        $siteSetting = $this->getSettingService()->get('site');

        return array(
            'name' => $siteSetting['name'],
            'url' => $siteSetting['url'],
            'logo' => empty($siteSetting['logo']) ? '' : $siteSetting['url'].'/'.$siteSetting['logo'],
        );
    }

    public function getWeChat($request)
    {
        $weChatSetting = $this->getSettingService()->get('wechat', array());

        $result = array(
            'enabled' => empty($weChatSetting['wechat_notification_enabled']) ? false : true,
            'official_qrcode' => empty($weChatSetting['account_code']) ? '' : $weChatSetting['account_code'],
        );

        $filter = new WeChatSettingFilter();
        $filter->filter($result);

        return $result;
    }

    public function getWap($request = null)
    {
        $wapSetting = $this->getSettingService()->get('wap', array('version' => 0));

        return array(
            'version' => empty($wapSetting['version']) ? array('version' => 0) : $wapSetting['version'],
        );
    }

    public function getRegister($request = null)
    {
        $registerSetting = $this->getSettingService()->get('auth', array('register_mode' => 'closed', 'email_enabled' => 'closed'));
        $registerMode = $registerSetting['register_mode'];
        $isEmailVerifyEnable = isset($registerSetting['email_enabled']) && 'opened' == $registerSetting['email_enabled'];
        $registerSetting = $this->getSettingService()->get('auth');
        $level = empty($registerSetting['register_protective']) ? 'none' : $registerSetting['register_protective'];
        $captchaEnabled = 'none' === $level ? false : true;

        return array(
            'mode' => $registerMode,
            'level' => $level,
            'captchaEnabled' => $captchaEnabled,
            'emailVerifyEnabled' => $isEmailVerifyEnable,
        );
    }

    /**
     * @return array
     * @ApiConf(isRequiredAuth=false)
     */
    public function getPayment($request = null)
    {
        $paymentSetting = $this->getSettingService()->get('payment', array());

        return array(
            'enabled' => empty($paymentSetting['enabled']) ? false : true,
            'alipayEnabled' => empty($paymentSetting['alipay_enabled']) ? false : true,
            'wxpayEnabled' => empty($paymentSetting['wxpay_enabled']) ? false : true,
            'llpayEnabled' => empty($paymentSetting['llpay_enabled']) ? false : true,
        );
    }

    public function getVip($request = null)
    {
        $vipSetting = $this->getSettingService()->get('vip', array());

        if (!empty($vipSetting['buyType'])) {
            switch ($vipSetting['buyType']) {
                case 10:
                    $buyType = 'year_and_month';
                    break;
                case 20:
                    $buyType = 'year';
                    break;
                case 30:
                    $buyType = 'month';
                    break;
                default:
                    $buyType = 'month';
                    break;
            }
        }

        return array(
            'enabled' => empty($vipSetting['enabled']) ? false : true,
            'h5Enabled' => empty($vipSetting['h5Enabled']) ? false : true,
            'buyType' => empty($buyType) ? 'month' : $buyType,
            'upgradeMinDay' => empty($vipSetting['upgrade_min_day']) ? '30' : $vipSetting['upgrade_min_day'],
            'defaultBuyYears' => empty($vipSetting['default_buy_years']) ? '1' : $vipSetting['default_buy_years'],
            'defaultBuyMonths' => empty($vipSetting['default_buy_months']) ? '30' : $vipSetting['default_buy_months'],
        );
    }

    public function getMagic($request = null)
    {
        $magicSetting = $this->getSettingService()->get('magic', array());
        $iosBuyDisable = isset($magicSetting['ios_buy_disable']) ? $magicSetting['ios_buy_disable'] : 0;
        $iosVipClose = isset($magicSetting['ios_vip_close']) ? $magicSetting['ios_vip_close'] : 0;

        return array(
            'iosBuyDisable' => $iosBuyDisable,
            'iosVipClose' => $iosVipClose,
        );
    }

    public function getCdn($request = null)
    {
        $cdn = $this->getSettingService()->get('cdn');

        return array(
            'enabled' => empty($cdn['enabled']) ? false : true,
            'defaultUrl' => empty($cdn['defaultUrl']) ? '' : $cdn['defaultUrl'],
            'userUrl' => empty($cdn['userUrl']) ? '' : $cdn['userUrl'],
            'contentUrl' => empty($cdn['contentUrl']) ? '' : $cdn['contentUrl'],
        );
    }

    public function getCourse($request = null)
    {
        $courseSetting = $this->getSettingService()->get('course', array());

        if (0 == $courseSetting['show_student_num_enabled']) {
            $showStudentNumEnabled = 0;
            $showHitNumEnabled = 0;
        } elseif ((1 == $courseSetting['show_student_num_enabled']) && (isset($courseSetting['show_cover_num_mode'])) && ('hitNum' == $courseSetting['show_cover_num_mode'])) {
            $showStudentNumEnabled = 0;
            $showHitNumEnabled = 1;
        } else {
            $showStudentNumEnabled = 1;
            $showHitNumEnabled = 0;
        }

        return array(
            'chapter_name' => empty($courseSetting['chapter_name']) ? '章' : $courseSetting['chapter_name'],
            'part_name' => empty($courseSetting['part_name']) ? '节' : $courseSetting['part_name'],
            'task_name' => empty($courseSetting['task_name']) ? '任务' : $courseSetting['task_name'],
            'show_student_num_enabled' => $showStudentNumEnabled,
            'show_hit_num_enabled' => $showHitNumEnabled,
        );
    }

    public function getFace($request = null)
    {
        $faceSetting = $this->getSettingService()->get('face', array());
        $featureSetting = $this->getSettingService()->get('feature', array());

        $settings = array(
            'login' => array(
                'enabled' => 0,
                'app_enabled' => 0,
                'pc_enabled' => 0,
                'h5_enabled' => 0,
            ),
        );

        if (isset($featureSetting['face_enabled']) && 1 == $featureSetting['face_enabled']) {
            $settings['login']['enabled'] = isset($faceSetting['login']['enabled']) ? $faceSetting['login']['enabled'] : 0;
            $settings['login']['app_enabled'] = isset($faceSetting['login']['app_enabled']) ? $faceSetting['login']['app_enabled'] : 0;
            $settings['login']['pc_enabled'] = isset($faceSetting['login']['pc_enabled']) ? $faceSetting['login']['pc_enabled'] : 0;
            $settings['login']['h5_enabled'] = isset($faceSetting['login']['h5_enabled']) ? $faceSetting['login']['h5_enabled'] : 0;
        }

        return $settings;
    }

    public function getWeixinConfig($request = null)
    {
        $params = $request->query->all();
        if (empty($params['url'])) {
            return array();
        }
        $result = $this->container->get('web.twig.extension')->weixinConfig($params['url']);
        if (is_array($result) || empty($result)) {
            return $result;
        }

        return json_decode($result, true);
    }

    public function getMiniprogram($request = null)
    {
        $authorizations = $this->getMpService()->getAuthorization();

        return array(
            'current_version' => empty($authorizations['current_version']) ? array('version' => '0.0.0') : $authorizations['current_version'],
            'newest_version' => empty($authorizations['newest_version']) ? array('version' => '0.0.0') : $authorizations['newest_version'],
        );
    }

    public function getLogin()
    {
        $clients = OAuthClientFactory::clients();

        return $this->getLoginConnect($clients);
    }

    public function getClassroom()
    {
        $classroomSetting = $this->getSettingService()->get('classroom', array());

        return array(
            'show_student_num_enabled' => isset($classroomSetting['show_student_num_enabled']) ? (bool) $classroomSetting['show_student_num_enabled'] : true,
        );
    }

    private function getLoginConnect($clients)
    {
        $default = $this->getDefaultLoginConnect($clients);
        $loginConnect = $this->getSettingService()->get('login_bind', array());
        $loginConnect = array_merge($default, $loginConnect);
        foreach ($clients as $type => $client) {
            if (isset($loginConnect["{$type}_secret"])) {
                unset($loginConnect["{$type}_secret"]);
            }
        }
        if (isset($loginConnect['weixinmob_mp_secret'])) {
            unset($loginConnect['weixinmob_mp_secret']);
        }

        return $loginConnect;
    }

    private function getDefaultLoginConnect($clients)
    {
        $default = array(
            'login_limit' => 0,
            'enabled' => 0,
            'verify_code' => '',
            'captcha_enabled' => 0,
            'temporary_lock_enabled' => 0,
            'temporary_lock_allowed_times' => 5,
            'ip_temporary_lock_allowed_times' => 20,
            'temporary_lock_minutes' => 20,
        );

        foreach ($clients as $type => $client) {
            $default["{$type}_enabled"] = 0;
            $default["{$type}_key"] = '';
            $default["{$type}_set_fill_account"] = 0;
        }

        return $default;
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    protected function getMpService()
    {
        return $this->service('Mp:MpService');
    }
}
