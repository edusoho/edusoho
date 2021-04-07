<?php

namespace AppBundle\Controller;

use AppBundle\Common\UserToolkit;
use AppBundle\Util\AvatarAlert;
use Biz\Order\OrderException;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserFieldService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Marketing\Service\VipRightService;

abstract class BuyFlowController extends BaseController
{
    protected $targetType = '';

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @todo 商品剥离控制购买入口需要改造
     */
    public function buyAction(Request $request, $id)
    {
        $this->checkUserLogin();

        if ($this->needApproval($id)) {
            $this->createNewException(OrderException::BEYOND_AUTHORITY);
        }

        if ($this->needNoStudentNumTip($id)) {
            $this->createNewException(OrderException::BEYOND_AUTHORITY);
        }

        if ($this->needUploadAvatar()) {
            $this->createNewException(OrderException::BEYOND_AUTHORITY);
        }

        if ($this->needFillUserInfo()) {
            $this->createNewException(OrderException::BEYOND_AUTHORITY);
        }

        if ($this->needOpenPayment($id)) {
            $this->createNewException(OrderException::BEYOND_AUTHORITY);
        }
//
//        if ('POST' == $request->getMethod()) {
//            $event = $this->needInformationCollectionBeforeJoin($id);
//            if (!empty($event)) {
//                return $this->createJsonResponse(['url' => $event['url']]);
//            }
//        }

        $this->tryFreeJoin($id);

        if ($this->isJoined($id)) {
            $event = $this->needInformationCollectionAfterJoin($id);
            if ('POST' === $request->getMethod()) {
                return !empty($event) ? $this->createJsonResponse(['url' => $event['url']]) : $this->createJsonResponse(['url' => $this->getSuccessUrl($id)]);
            }

            return !empty($event) ? $this->redirect($event['url']) : $this->redirect($this->getSuccessUrl($id));
        }

        return $this->createJsonResponse(['url' => $this->generateUrl('order_show', ['targetId' => $id, 'targetType' => $this->targetType])]);
    }

    protected function needUploadAvatar()
    {
        return AvatarAlert::alertJoinCourse($this->getUser());
    }

    protected function needFillUserInfo()
    {
        $setting = $this->getSettingService()->get('course');

        if (!empty($setting['buy_fill_userinfo'])) {
            $user = $this->getUser();
            $userInfo = $this->getUserService()->getUserProfile($user['id']);
            if (!empty($user['verifiedMobile']) && empty($userInfo['mobile'])) {
                $userInfo = $this->getUserService()->updateUserProfile($user['id'], [
                    'mobile' => $user['verifiedMobile'],
                ]);
            }
            $user = array_merge($userInfo, $user->toArray());
            $buyFields = $setting['userinfoFields'];
            foreach ($buyFields as $buyField) {
                if (empty($user[$buyField])) {
                    return true;
                }
            }

            if (in_array('email', $buyFields) && UserToolkit::isEmailGeneratedBySystem($user['email'])) {
                return true;
            }

            if (in_array('gender', $buyFields) && UserToolkit::isGenderDefault($user['gender'])) {
                return true;
            }
        }

        return false;
    }

    protected function needNoStudentNumTip($id)
    {
        return false;
    }

    protected function needApproval($id)
    {
        return false;
    }

    protected function needOpenPayment($id)
    {
        return false;
    }

    private function checkUserLogin()
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return VipRightService
     */
    protected function getVipRightService()
    {
        return $this->createService('VipPlugin:Marketing:VipRightService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    abstract protected function getSuccessUrl($id);

    abstract protected function isJoined($id);

    abstract protected function tryFreeJoin($id);

    abstract protected function needInformationCollectionBeforeJoin($targetId);

    abstract protected function needInformationCollectionAfterJoin($targetId);
}
