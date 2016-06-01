<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OperationSettingController extends BaseController
{
    public function articleSetAction(Request $request)
    {
        $articleSetting = $this->getSettingService()->get('article', array());

        $default = array(
            'name'     => '资讯频道',
            'pageNums' => 20
        );

        $articleSetting = array_merge($default, $articleSetting);

        if ($request->getMethod() == 'POST') {
            $articleSetting = $request->request->all();
            $this->getSettingService()->set('article', $articleSetting);
            $this->getLogService()->info('article', 'update_settings', "更新资讯频道设置", $articleSetting);
            $this->setFlashMessage('success', '资讯频道设置已保存！');
        };

        return $this->render('TopxiaAdminBundle:Article:setting.html.twig', array(
            'articleSetting' => $articleSetting
        ));
    }

    public function groupSetAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $set = $request->request->all();

            $this->getSettingService()->set('group', $set);
            $this->setFlashMessage('success', '小组设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:Group:set.html.twig', array(
        ));
    }

    public function inviteSetAction(Request $request)
    {
        $default = array(
            'invite_code_setting'       => 0,
            'promoted_user_value'       => '',
            'promote_user_value'        => '',
            'get_coupon_setting'        => 1,
            'deadline'                  => 90,
            'inviteInfomation_template' => '{{registerUrl}}'
        );

        if ($request->getMethod() == 'POST') {
            $inviteSetting = $request->request->all();
            if(isset($inviteSetting['get_coupon_setting'])){
                $inviteSetting['get_coupon_setting'] = 1;
            }else{
                $inviteSetting['get_coupon_setting'] = 0;
            }
            $inviteSetting = ArrayToolkit::parts($inviteSetting, array(
                'invite_code_setting',
                'promoted_user_value',
                'promote_user_value',
                'get_coupon_setting',
                'deadline',
                'inviteInfomation_template'
            ));

            $inviteSetting = array_merge($default, $inviteSetting);

            $this->getSettingService()->set('invite', $inviteSetting);
            $this->setFlashMessage('success', '邀请码设置已保存！');
            goto response;
        }

        $inviteSetting = $this->getSettingService()->get('invite', array());
        $inviteSetting = array_merge($default, $inviteSetting);

        response:
        return $this->render('TopxiaAdminBundle:Invite:set.html.twig', array(
            'inviteSetting'             => $inviteSetting,
            'inviteInfomation_template' => $inviteSetting['inviteInfomation_template']
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }
}
