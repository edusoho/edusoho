<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Coupon\Service\CouponBatchService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class OperationSettingController extends BaseController
{
    public function articleSetAction(Request $request)
    {
        $articleSetting = $this->getSettingService()->get('article', array());

        $default = array(
            'name' => '资讯频道',
            'pageNums' => 20,
            'show_comment' => '1',
        );

        $articleSetting = array_merge($default, $articleSetting);

        if ('POST' == $request->getMethod()) {
            $articleSetting = $request->request->all();
            $this->getSettingService()->set('article', $articleSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/operation/article-setting.html.twig', array(
            'articleSetting' => $articleSetting,
        ));
    }

    public function groupSetAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $this->getSettingService()->set('group', $set);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/operation/group-setting.html.twig', array(
        ));
    }

    public function inviteSetAction(Request $request)
    {
        $default = array(
            'invite_code_setting' => 0,
            'promoted_user_enable' => 0,
            'promoted_user_batchId' => '',
            'promote_user_enable' => 0,
            'promote_user_batchId' => '',
            'get_coupon_setting' => 1,
            'inviteInfomation_template' => '{{registerUrl}}',
            'remain_number' => '',
            'mobile' => '',
        );

        if ('POST' == $request->getMethod()) {
            $inviteSetting = $request->request->all();
            if (!empty($inviteSetting['promoted_user_batchId']) || !empty($inviteSetting['promoted_user_enable'])) {
                $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promoted_user_batchId']);
                if ($batch['unreceivedNum'] <= 1) {
                    return  $this->createJsonResponse(array('status' => false, 'message' => $this->trans('admin.setting.invite.chooser_coupon.unreceived_num')));
                }
            }
            if (!empty($inviteSetting['promote_user_batchId']) || !empty($inviteSetting['promote_user_enable'])) {
                $batch = $this->getCouponBatchService()->getBatch($inviteSetting['promote_user_batchId']);
                if ($batch['unreceivedNum'] <= 1) {
                    return  $this->createJsonResponse(array('status' => false, 'message' => $this->trans('admin.setting.invite.chooser_coupon.unreceived_num')));
                }
            }
            $inviteSetting = ArrayToolkit::parts($inviteSetting, array(
                'invite_code_setting',
                'promoted_user_enable',
                'promoted_user_batchId',
                'promote_user_enable',
                'promote_user_batchId',
                'get_coupon_setting',
                'inviteInfomation_template',
                'remain_number',
                'mobile',
            ));

            $inviteSetting = array_merge($default, $inviteSetting);
            $inviteSetting['promoted_sms_send'] = 1;
            $inviteSetting['promote_sms_send'] = 1;
            if (!empty($inviteSetting['remain_number']) && !empty($inviteSetting['mobile'])) {
                $inviteSetting = $this->updateInviteSmsSendSetting($inviteSetting);
            }

            $this->getSettingService()->set('invite', $inviteSetting);

            return $this->createJsonResponse(true);
        }

        $inviteSetting = $this->getSettingService()->get('invite', array());
        $inviteSetting = array_merge($default, $inviteSetting);

        return $this->render('admin-v2/system/operation/invite-setting.html.twig', array(
            'inviteSetting' => $inviteSetting,
            'inviteInfomation_template' => $inviteSetting['inviteInfomation_template'],
        ));
    }

    public function messageSettingAction(Request $request)
    {
        $message = $this->getSettingService()->get('message', array());

        $default = array(
            'showable' => '1',
        );

        $message = array_merge($default, $message);

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $message = array_merge($message, $set);

            $this->getSettingService()->set('message', $set);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/operation/message-setting.html.twig', array(
            'messageSetting' => $message,
        ));
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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CouponBatchService
     */
    protected function getCouponBatchService()
    {
        return $this->createService('Coupon:CouponBatchService');
    }
}
