<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Biz\WeChat\Service\WeChatService;
use Symfony\Component\HttpFoundation\Request;

class WechatFansController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->filterConditions($conditions);
        $conditions['subscribeTimeNotEqual'] = 0;
        $wechatSetting = $this->getSettingService()->get('wechat', array());

        if (isset($wechatSetting['wechat_notification_enabled']) && 1 == $wechatSetting['wechat_notification_enabled']) {
            $currentNum = $this->getWeChatService()->countWeChatUserJoinUser($conditions);
            $paginator = new Paginator(
                $request,
                $currentNum,
                10
            );

            $fans = $this->getWeChatService()->searchWeChatUsersJoinUser(
                $conditions,
                array('subscribeTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        return $this->render('admin-v2/user/fans-list/fans-list.html.twig', array(
            'fans' => isset($fans) ? $fans : array(),
            'paginator' => isset($paginator) ? $paginator : array(),
            'currentNum' => isset($currentNum) ? $currentNum : 0,
            'wechatSetting' => $wechatSetting,
        ));
    }

    protected function filterConditions($conditions)
    {
        if (isset($conditions['weChatFansType'])) {
            if ('user' == $conditions['weChatFansType']) {
                $conditions['userIdNotEqual'] = 0;
            }

            if ('notUser' == $conditions['weChatFansType']) {
                $conditions['userId'] = 0;
            }

            unset($conditions['weChatFansType']);
        }

        if (isset($conditions['weChatFansKeywordType'])) {
            if ('wechatNickname' == $conditions['weChatFansKeywordType']) {
                $conditions['wechatname'] = urlencode($conditions['keyword']);
            }

            if ('nickname' == $conditions['weChatFansKeywordType']) {
                $conditions['nickname'] = $conditions['keyword'];
            }

            if (!empty($conditions['keyword'])) {
                unset($conditions['keyword']);
            }
        }
        unset($conditions['weChatFansKeywordType']);

        return $conditions;
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->createService('WeChat:WeChatService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
