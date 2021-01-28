<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Coupon\Service\CouponBatchService;
use Symfony\Component\HttpFoundation\Request;

class OperationSettingController extends BaseController
{
    public function wapSetAction(Request $request)
    {
        $defaultWapSetting = [
            'version' => 1,
            'template' => 'jianmoOn',
        ];

        if ($request->isMethod('POST')) {
            $wapSetting = $request->request->all();
            $wapSetting = ArrayToolkit::parts($wapSetting, [
                'version', 'template',
            ]);

            $template = $wapSetting['template'];
            $wapSetting = array_merge($defaultWapSetting, $wapSetting);
            $this->getSettingService()->set('wap', $wapSetting);
            $result = CloudAPIFactory::create('leaf')->get('/me');
            if (empty($result['error'])) {
                $this->getSettingService()->set('meCount', $result);
            }
        }

        $wapSetting = $this->setting('wap', []);
        $wapSetting = array_merge($defaultWapSetting, $wapSetting);

        return $this->render('admin/wap/set.html.twig', [
            'wapSetting' => $wapSetting,
            'template' => empty($template) ? '' : $template,
        ]);
    }

    public function articleSetAction(Request $request)
    {
        $articleSetting = $this->getSettingService()->get('article', []);

        $default = [
            'name' => '资讯频道',
            'pageNums' => 20,
            'show_comment' => '1',
        ];

        $articleSetting = array_merge($default, $articleSetting);

        if ('POST' == $request->getMethod()) {
            $articleSetting = $request->request->all();
            $this->getSettingService()->set('article', $articleSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/article/setting.html.twig', [
            'articleSetting' => $articleSetting,
        ]);
    }

    public function groupSetAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $this->getSettingService()->set('group', $set);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/group/set.html.twig', [
        ]);
    }

    public function inviteSetAction(Request $request)
    {
        $default = [
            'invite_code_setting' => 0,
            'promoted_user_enable' => 0,
            'promoted_user_batchId' => '',
            'promote_user_enable' => 0,
            'promote_user_batchId' => '',
            'get_coupon_setting' => 1,
            'inviteInfomation_template' => '{{registerUrl}}',
            'remain_number' => '',
            'mobile' => '',
        ];

        if ('POST' == $request->getMethod()) {
            $inviteSetting = $request->request->all();
            if (!empty($inviteSetting['promoted_user_batchId']) || !empty($inviteSetting['promoted_user_enable'])) {
                $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promoted_user_batchId']);
                if ($batch['unreceivedNum'] <= 1) {
                    return  $this->createJsonResponse(['status' => false, 'message' => $this->trans('admin.setting.invite.chooser_coupon.unreceived_num')]);
                }
            }
            if (!empty($inviteSetting['promote_user_batchId']) || !empty($inviteSetting['promote_user_enable'])) {
                $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promote_user_batchId']);
                if ($batch['unreceivedNum'] <= 1) {
                    return  $this->createJsonResponse(['status' => false, 'message' => $this->trans('admin.setting.invite.chooser_coupon.unreceived_num')]);
                }
            }
            $inviteSetting = ArrayToolkit::parts($inviteSetting, [
                'invite_code_setting',
                'promoted_user_enable',
                'promoted_user_batchId',
                'promote_user_enable',
                'promote_user_batchId',
                'get_coupon_setting',
                'inviteInfomation_template',
                'remain_number',
                'mobile',
            ]);

            $inviteSetting = array_merge($default, $inviteSetting);
            $inviteSetting['promoted_sms_send'] = 1;
            $inviteSetting['promote_sms_send'] = 1;
            if (!empty($inviteSetting['remain_number']) && !empty($inviteSetting['mobile'])) {
                $inviteSetting = $this->updateInviteSmsSendSetting($inviteSetting);
            }

            $this->getSettingService()->set('invite', $inviteSetting);

            return $this->createJsonResponse(true);
        }

        $inviteSetting = $this->getSettingService()->get('invite', []);
        $inviteSetting = array_merge($default, $inviteSetting);

        return $this->render('admin/invite/set.html.twig', [
            'inviteSetting' => $inviteSetting,
            'inviteInfomation_template' => $inviteSetting['inviteInfomation_template'],
        ]);
    }

    protected function updateInviteSmsSendSetting($inviteSetting)
    {
        if ($inviteSetting['promoted_user_enable']) {
            $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promoted_user_batchId']);
            if (!empty($batch) && $inviteSetting['remain_number'] <= $batch['unreceivedNum']) {
                $inviteSetting['promoted_sms_send'] = 0;
            }
        }

        if ($inviteSetting['promote_user_enable']) {
            $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promote_user_batchId']);
            if (!empty($batch) && $inviteSetting['remain_number'] <= $batch['unreceivedNum']) {
                $inviteSetting['promote_sms_send'] = 0;
            }
        }

        return $inviteSetting;
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }

    /**
     * @return CouponBatchService
     */
    protected function getCouponBatchService()
    {
        return $this->createService('Coupon:CouponBatchService');
    }
}
