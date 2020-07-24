<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\UserToolkit;
use AppBundle\Util\AvatarAlert;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Goods\Service\GoodsService;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserFieldService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GoodBuy extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $params = $request->request->all();

        if (empty($params['targetId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $goods = $this->getGoodsService()->getGoods($id);
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecs($params['targetId']);
        $params['targetType'] = $goods['type'];

        if ($this->needNoStudentNumTip($goods['type'], $goodsSpecs['targetId'])) {
            return ['success' => false, 'noticeTemplate' => 'no-remain', 'url' => ''];
        }

        if ($this->needUploadAvatar()) {
            return ['success' => false, 'noticeTemplate' => 'avatar-alert', 'url' => ''];
        }

        if ($this->needFillUserInfo()) {
            return ['success' => false, 'noticeTemplate' => 'fill-user-info', 'url' => ''];
        }

        if ($this->needOpenPayment($goodsSpecs)) {
            return ['success' => false, 'noticeTemplate' => 'payments-disabled', 'url' => ''];
        }

        $this->tryFreeJoin($goods['type'], $goodsSpecs['targetId']);

        if ($this->isJoined($goods['type'], $goodsSpecs['targetId'])) {
            return ['success' => false, 'noticeTemplate' => '', 'url' => $this->getSuccessUrl($goods['type'], $goodsSpecs['targetId'])];
        }

        return [
            'success' => true,
            'noticeTemplate' => '',
            'url' => $this->generateUrl('order_show', $params),
        ];
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
            return $this->generateUrl('my_course_show', ['id' => $id]);
        } elseif ('classroom' == $type) {
            return $this->generateUrl('classroom_show', ['id' => $id]);
        }
    }

    protected function getProductByParams($params)
    {
        try {
            $product = $this->getProduct($params['targetType'], $params);
            $product->validate();
            $product->setAvailableDeduct();
            $product->setPickedDeduct([]);

            return $product;
        } catch (OrderPayCheckException $payCheckException) {
            throw new BadRequestHttpException($payCheckException->getMessage(), $payCheckException, $payCheckException->getCode());
        }
    }

    private function getProduct($targetType, $params)
    {
        $biz = $this->getBiz();

        /* @var $product Product */
        $product = $biz['order.product.'.$targetType];

        $product->init($params);

        return $product;
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
