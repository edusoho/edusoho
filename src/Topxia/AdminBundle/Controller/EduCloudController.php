<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class EduCloudController extends BaseController
{
    public function indexAction(Request $request)
    {
        try {
            $account = $this->getAccount();

            if(!empty($account)) {
                $money = isset($account['cash']) ? $account['cash'] : '--';

                $loginToken = $this->getAppService()->getLoginToken();

                $result = $this->getSmsOpenStatus();
                if (isset($result['apply']) && isset($result['apply']['status'])) {
                    $smsStatus['status'] = $result['apply']['status'];
                    $smsStatus['message'] = $result['apply']['message'];
                } else if (isset($result['error'])) {
                    $smsStatus['status'] = 'error';
                    $smsStatus['message'] = $result['error'];
                }
            }
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        }

        return $this->render('TopxiaAdminBundle:EduCloud:edu-cloud.html.twig', array(
            'account' => $account,
            'token' => isset($loginToken) && isset($loginToken["token"]) ? $loginToken["token"] : '',
            'smsStatus' => isset($smsStatus) ? $smsStatus : null,
        ));
    }

    public function smsAction(Request $request)
    {
        $settings = $this->getSettingService()->get('storage', array());
        if (empty($settings['cloud_access_key']) or empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', '您还没有授权码，请先绑定。');
            return $this->redirect($this->generateUrl('admin_setting_cloud_key_update'));
        }
        
        try {
            $smsStatus = $this->handleSmsSetting($request);

            return $this->render('TopxiaAdminBundle:EduCloud:sms.html.twig', array(
                'smsStatus' => $smsStatus,
            ));
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        }

    }

    public function applyForSmsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $result = null;
            $dataUserPosted = $request->request->all();

            if (
                isset($dataUserPosted['name'])
                && ($this->calStrlen($dataUserPosted['name']) >= 2)
                && ($this->calStrlen($dataUserPosted['name']) <= 16)
            ) {
                $result = $this->applyForSms($dataUserPosted['name']);
                if (isset($result['status']) && ($result['status'] == 'ok')) {
                    $this->setCloudSmsKey('sms_school_candidate_name', $dataUserPosted['name']);
                    $this->setCloudSmsKey('show_message', 'on');
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

    private function handleSmsSetting(Request $request)
    {
        list($smsStatus, $schoolNames) = $this->getSchoolName();

        if ($request->getMethod() == 'POST') {
            $dataUserPosted = $request->request->all();

            $defaultSetting = array(
                'sms_enabled' => '0',
                'sms_registration' => 'off',
                'sms_forget_password' => 'off',
                'sms_user_pay' => 'off',
                'sms_forget_pay_password' => 'off',
                'sms_bind' => 'off',
            );
            $dataUserPosted = ArrayToolKit::filter($dataUserPosted, $defaultSetting);

            $dataUserPosted = array_merge($dataUserPosted, $schoolNames);

            $this->getSettingService()->set('cloud_sms', $dataUserPosted);
            
            if ('1' == $dataUserPosted['sms_enabled']) {
                $this->setFlashMessage('success', '短信功能开启成功，每条短信0.07元。');
            } else {
                $this->setFlashMessage('success', '设置成功。');
            }
        } 
        return $smsStatus;
    }

    public function smsNoMessageAction(Request $request)
    {
        $this->setCloudSmsKey('show_message', 'off');
        return $this->redirect($this->generateUrl('admin_edu_cloud_sms', array()));
    }

    public function smsBillAction(Request $request)
    {
        try {
            
            $loginToken = $this->getAppService()->getLoginToken();
            $account = $this->getAccount();

            $result = $this->getBills($type='sms', 1, 20);

            $paginator = new Paginator(
                $this->get('request'),
                $result["total"],
                20
            );

            $result = $this->getBills($type='sms', $paginator->getCurrentPage(), $paginator->getPerPageCount());
            $bills = $result['items'];
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        }
           
        return $this->render('TopxiaAdminBundle:EduCloud:sms-bill.html.twig', array(
            'account' => $account,
            'token' => isset($loginToken) && isset($loginToken["token"]) ? $loginToken["token"] : '',
            'bills' => $bills,
            'paginator' => $paginator,
        ));
    }

    private function getSchoolName()
    {
        $schoolName = $this->getCloudSmsKey('sms_school_name');
        $schoolCandidateName = $this->getCloudSmsKey('sms_school_candidate_name');
        $result = $this->getSmsOpenStatus();
        $smsStatus = array();
        if (isset($result['apply']) && isset($result['apply']['status'])) {
            $smsStatus['status'] = $result['apply']['status'];
            if (($smsStatus['status'] == 'passed')&&(strlen($schoolCandidateName) > 0)) {
                $schoolName = $schoolCandidateName;
                $schoolCandidateName = '';
                $this->setCloudSmsKey('sms_school_name', $schoolName);
                $this->setCloudSmsKey('sms_school_candidate_name', '');
            }
            if (isset($result['apply']['message'])) {
                $smsStatus['message'] = $result['apply']['message'];
                if (strlen($smsStatus['message']) > 0){
                    $smsStatus['message'] = $smsStatus['message'];
                }
            }
            if ($smsStatus['status'] == 'failed') {
                $info = '您新申请的网校名称“'.$schoolCandidateName.'”未通过审核，原因是：';
                if(isset($smsStatus['message']) && $smsStatus['message']) {
                    $info .= $smsStatus['message'];
                } else {
                    $info .= '网校名称不符合规范';
                }
                $smsStatus['schoolNameError'] = $info;
                
            }
        } else if (isset($result['error'])) {
            $smsStatus['status'] = 'error';
            $smsStatus['message'] = $result['error'];
        }

        return array(
            $smsStatus, 
            array(
                'sms_school_name' => $schoolName,
                'sms_school_candidate_name' => $schoolCandidateName,
            )
        );
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

    private function getAccount()
    {
        return $this->getEduCloudService()->getAccount();
    }

    private function applyForSms($name = 'smsHead')
    {
        return $this->getEduCloudService()->applyForSms($name);
    }

    private function getSmsOpenStatus()
    {
        return $this->getEduCloudService()->getSmsOpenStatus();
    }

    private function getBills($type = 'sms', $page=1, $limit=20)
    {
        return $this->getEduCloudService()->getBills($type, $page, $limit);
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
