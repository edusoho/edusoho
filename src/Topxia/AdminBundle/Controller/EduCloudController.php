<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EduCloudController extends BaseController
{
    private $cloudOptions = null;
    private $cloudApi = null;
    private $debug = true;

    public function indexAction(Request $request)
    {
        $loginToken = $this->getAppService()->getLoginToken();
        $hasAccount = isset($loginToken["token"]);

        $money = '--';
        try {
            $result = $this->getAccounts();
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        }
        if (isset($result['cash'])) {
            $money = $result['cash'];
        }

        $smsStatus = array();
        try {
            $result = $this->lookForStatus();
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        }
        if (isset($result['apply']) && isset($result['apply']['status'])) {
            $smsStatus['status'] = $result['apply']['status'];
            $smsStatus['message'] = $result['apply']['message'];
        } else if (isset($result['error'])) {
            $smsStatus['status'] = 'error';
            $smsStatus['message'] = $result['error'];
        }

        if ($this->debug) {
            $hasAccount = true;
            $loginToken["token"] = '8888';
        }

        return $this->render('TopxiaAdminBundle:EduCloud:edu-cloud.html.twig', array(
            'money' => $money,
            'hasAccount' => $hasAccount,
            'token' => $hasAccount ? $loginToken["token"] : '',
            'smsStatus' => $smsStatus,
        ));
    }

    public function smsAction(Request $request)
    {
        $this->handleSmsSetting($request);
        $smsStatus = array();

        try {
            $result = $this->lookForStatus();
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        }

        if (isset($result['apply']) && isset($result['apply']['status'])) {
            $smsStatus['status'] = $result['apply']['status'];
        } else if (isset($result['error'])) {
            $smsStatus['status'] = 'error';
            $smsStatus['message'] = $result['error'];
        }

        return $this->render('TopxiaAdminBundle:EduCloud:sms.html.twig', array(
            'smsStatus' => $smsStatus,
        ));
    }

    public function applyForSmsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $result = null;
            $dataUserPosted = $request->request->all();

            if (
                isset($dataUserPosted['name'])
                && ($this->calStrlen($dataUserPosted['name']) >= 2)
                && ($this->calStrlen($dataUserPosted['name']) <= 8)
            ) {
                $result = $this->applyForSms($dataUserPosted['name']);
                if (isset($result['status']) && ($result['status'] == 'ok')) {
                    $this->setCloudSmsKey('sms_school_name', $dataUserPosted['name']);
                    return $this->createJsonResponse(array('ACK' => 'ok'));
                }
            }

            return $this->createJsonResponse(array(
                'ACK' => 'failed',
                'message' => $result['error'] . '|' . ($this->calStrlen($dataUserPosted['name'])),
            ));
        }
        return $this->render('TopxiaAdminBundle:EduCloud:apply-sms-form.html.twig', array());
    }

    public function smsUsageAction(Request $request)
    {
        //8888888888
    }

    public function smsSwitchAction(Request $request, $open)
    {
        //8888888888
    }

    public function smsCaptchaAction(Request $request)
    {
        //8888888888
    }

    private function handleSmsSetting(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $dataUserPosted = $request->request->all();
            $this->setCloudSmsKey('sms_enabled', $dataUserPosted['sms_enabled']);

            $auth = $this->getSettingService()->get('auth', array());
            if (isset($dataUserPosted['sms_registration']) && ($dataUserPosted['sms_registration'] == 'on')) {
                $this->setCloudSmsKey('sms_registration', 'on');
                if (!in_array('mobile', $auth['registerSort'])) {
                    $auth['registerSort'][] = 'mobile';
                }
            } else {
                $this->setCloudSmsKey('sms_registration', 'off');
                if (in_array('mobile', $auth['registerSort'])) {
                    $index = array_search('mobile',$auth['registerSort']);
                    unset($auth['registerSort'][$index]);
                }
            }
            $this->getSettingService()->set('auth', $auth);

            if (isset($dataUserPosted['sms_forget_password']) && ($dataUserPosted['sms_forget_password'] == 'on')) {
                $this->setCloudSmsKey('sms_forget_password', 'on');
            } else {
                $this->setCloudSmsKey('sms_forget_password', 'off');
            }
            if (isset($dataUserPosted['sms_user_pay']) && ($dataUserPosted['sms_user_pay'] == 'on')) {
                $this->setCloudSmsKey('sms_user_pay', 'on');
            } else {
                $this->setCloudSmsKey('sms_user_pay', 'off');
            }
            if (isset($dataUserPosted['sms_forget_pay_password']) && ($dataUserPosted['sms_forget_pay_password'] == 'on')) {
                $this->setCloudSmsKey('sms_forget_pay_password', 'on');
            } else {
                $this->setCloudSmsKey('sms_forget_pay_password', 'off');
            }
            if (isset($dataUserPosted['sms_bind']) && ($dataUserPosted['sms_bind'] == 'on')) {
                $this->setCloudSmsKey('sms_bind', 'on');
            } else {
                $this->setCloudSmsKey('sms_bind', 'off');
            }

            if ('1' == $dataUserPosted['sms_enabled']) {
                $this->setFlashMessage('success', '短信功能开启成功，每条短信0.07元。');
            } else {
                $this->setFlashMessage('success', '设置成功。');
            }
        }
    }

    private function calStrlen($str)
    {
        return (strlen($str) + mb_strlen($str, 'UTF8')) / 2;
    }

    private function setCloudSmsKey($key, $val)
    {
        $setting = $this->getSettingService()->get('cloud_sms', array());
        $setting[$key] = $val;
        $this->getSettingService()->set('cloud_sms', $setting);
    }

    private function getCloudSmsKey($key)
    {
        $setting = $this->getSettingService()->get('cloud_sms', array());
        if (isset($setting[$key])){
            return $setting[$key];
        }
        return null;
    }

    private function getAccounts()
    {
        return $this->getEduCloudService()->getAccounts();
    }

    private function applyForSms($name = 'smsHead')
    {
        return $this->getEduCloudService()->applyForSms($name);
    }

    private function lookForStatus()
    {
        return $this->getEduCloudService()->lookForStatus();
    }

    private function sendSms($to, $verify, $category = 'verify')
    {
        return $this->getEduCloudService()->sendSms($to, $verify, $category);
    }

    private function verifyKeys()
    {
        return $this->getEduCloudService()->verifyKeys();
    }

    protected function getEduCloudService()
    {
        return $this->getServiceKernel()->createService('EduCloud.EduCloudService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
