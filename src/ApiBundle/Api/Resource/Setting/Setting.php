<?php

namespace ApiBundle\Api\Resource\Setting;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Setting extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $type)
    {
        if (!in_array($type, array('site', 'wap', 'register', 'payment', 'vip', 'magic', 'cdn', 'course'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        $method = "get${type}";

        return $this->$method();
    }

    public function getSite()
    {
        $siteSetting = $this->getSettingService()->get('site');

        return array(
            'name' => $siteSetting['name'],
            'url' => $siteSetting['url'],
            'logo' => empty($siteSetting['logo']) ? '' : $siteSetting['url'].'/'.$siteSetting['logo'],
        );
    }

    public function getWap()
    {
        $wapSetting = $this->getSettingService()->get('wap', array('version' => 0));

        return array(
            'version' => empty($wapSetting['version']) ? array('version' => 0) : $wapSetting['version'],
        );
    }

    public function getRegister()
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
    public function getPayment()
    {
        $paymentSetting = $this->getSettingService()->get('payment', array());

        return array(
            'enabled' => empty($paymentSetting['enabled']) ? false : true,
            'alipayEnabled' => empty($paymentSetting['alipay_enabled']) ? false : true,
            'wxpayEnabled' => empty($paymentSetting['wxpay_enabled']) ? false : true,
            'llpayEnabled' => empty($paymentSetting['llpay_enabled']) ? false : true,
        );
    }

    public function getVip()
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
            'buyType' => empty($buyType) ? 'month' : $buyType,
            'upgradeMinDay' => empty($vipSetting['upgrade_min_day']) ? '30' : $vipSetting['upgrade_min_day'],
            'defaultBuyYears' => empty($vipSetting['default_buy_years']) ? '1' : $vipSetting['default_buy_years'],
            'defaultBuyMonths' => empty($vipSetting['default_buy_months']) ? '30' : $vipSetting['default_buy_months'],
        );
    }

    public function getMagic()
    {
        $magicSetting = $this->getSettingService()->get('magic', array());
        $iosBuyDisable = isset($magicSetting['ios_buy_disable']) ? $magicSetting['ios_buy_disable'] : 0;
        $iosVipClose = isset($magicSetting['ios_vip_close']) ? $magicSetting['ios_vip_close'] : 0;

        return array(
            'iosBuyDisable' => $iosBuyDisable,
            'iosVipClose' => $iosVipClose,
        );
    }

    public function getCdn()
    {
        $cdn = $this->getSettingService()->get('cdn');

        return array(
            'enabled' => empty($cdn['enabled']) ? false : true,
            'defaultUrl' => empty($cdn['defaultUrl']) ? '' : $cdn['defaultUrl'],
            'userUrl' => empty($cdn['userUrl']) ? '' : $cdn['userUrl'],
            'contentUrl' => empty($cdn['contentUrl']) ? '' : $cdn['contentUrl'],
        );
    }

    public function getCourse()
    {
        $courseSetting = $this->getSettingService()->get('course', array());

        return array(
            'chapter_name' => empty($courseSetting['chapter_name']) ? '章' : $courseSetting['chapter_name'],
            'part_name' => empty($courseSetting['part_name']) ? '节' : $courseSetting['part_name'],
            'task_name' => empty($courseSetting['task_name']) ? '任务' : $courseSetting['task_name'],
            'show_student_num_enabled' => '1',
        );
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
