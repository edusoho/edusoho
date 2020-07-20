<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\UserToolkit;
use AppBundle\Util\AvatarAlert;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Goods\Service\GoodsService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserFieldService;
use Biz\User\Service\UserService;

class GoodBuy extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecs($id);
        $goods = $this->getGoodsService()->getGoods($goodsSpecs['goodsId']);

        if (DeviceToolkit::isMobileClient()) {
            return $this->forwardOrderInfo($goodsSpecs, $goods);
        }

        return $this->forwardWebOrderShow($goodsSpecs, $goods);
    }

    protected function forwardOrderInfo($goodsSpecs, $goods)
    {
        return $this->invokeResource(new ApiRequest(
            '/api/order_infos',
            'POST',
            [],
            [
                'targetType' => $goods['type'],
                'targetId' => $goodsSpecs['id'],
            ]
        ));
    }

    protected function forwardWebOrderShow($goodsSpecs, $goods)
    {
        if ($this->needNoStudentNumTip($goods['type'], $goodsSpecs['targetId'])) {
            return $this->renderView('buy-flow/no-remain-modal.html.twig');
        }

        if ($this->needUploadAvatar()) {
            return $this->renderView('buy-flow/avatar-alert-modal.html.twig');
        }

        if ($this->needFillUserInfo()) {
            $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
            $user = $this->getCurrentUser();
            $userInfo = $this->getUserService()->getUserProfile($user['id']);
            $userInfo['approvalStatus'] = $user['approvalStatus'];

            return $this->renderView('buy-flow/fill-user-info-modal.html.twig', [
                'userFields' => $userFields,
                'user' => $userInfo,
            ]);
        }

        if ($this->needOpenPayment($goodsSpecs)) {
            return $this->renderView('buy-flow/payments-disabled-modal.html.twig');
        }

        $this->tryFreeJoin($goods['type'], $goodsSpecs['targetId']);

        if ($this->isJoined($goods['type'], $goodsSpecs['targetId'])) {
            return $this->getSuccessUrl($goods['type'], $goodsSpecs['targetId']);
        }

        return ['url' => $this->generateUrl('order_show', ['targetId' => $goodsSpecs['id'], 'targetType' => $goods['type']])];
    }

    protected function needNoStudentNumTip($type, $id)
    {
        if ('course' !== $type) {
            return false;
        }

        $course = $this->getCourseService()->getCourse($id);

        return $course['maxStudentNum'] - $course['studentNum'] <= 0 && 'live' == $course['type'];
    }

    protected function needUploadAvatar()
    {
        return AvatarAlert::alertJoinCourse($this->getCurrentUser());
    }

    protected function needFillUserInfo()
    {
        $setting = $this->getSettingService()->get('course');

        if (!empty($setting['buy_fill_userinfo'])) {
            $user = $this->getCurrentUser();
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

    protected function needOpenPayment($goodsSpecs)
    {
        $payment = $this->getSettingService()->get('payment');
        $vipJoinEnabled = false;
//        TODO: Marketing Setting

        return (float) $goodsSpecs['price'] > 0.00 && !$payment['enabled'] && !$vipJoinEnabled;
    }

    protected function tryFreeJoin($type, $id)
    {
        if ('course' == $type) {
            $this->getCourseService()->tryFreeJoin($id);
        } elseif ('classroom' == $type) {
            $this->getClassroomService()->tryFreeJoin($id);
        }
    }

    protected function isJoined($type, $id)
    {
        if ('course' == $type) {
            return $this->getCourseMemberService()->getCourseMember($id, $this->getCurrentUser()->getId());
        } elseif ('classroom' == $type) {
            return $this->getClassroomService()->isClassroomStudent($id, $this->getCurrentUser()->getId());
        }
    }

    protected function getSuccessUrl($type, $id)
    {
        if ('course' == $type) {
            return ['url' => $this->generateUrl('my_course_show', ['id' => $id])];
        } elseif ('classroom' == $type) {
            return ['url' => $this->generateUrl('classroom_show', ['id' => $id])];
        }
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->service('User:UserFieldService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
