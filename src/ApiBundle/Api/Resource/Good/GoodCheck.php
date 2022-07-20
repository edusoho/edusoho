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
use Biz\Goods\GoodsException;
use Biz\Goods\Service\GoodsService;
use Biz\InformationCollect\Service\EventService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserFieldService;
use Biz\User\Service\UserService;

class GoodCheck extends AbstractResource
{
    const NO_REMAIN = 'no-remain';

    const AVATAR_ALERT = 'avatar-alert';

    const FILL_USER_INFO = 'fill-user-info';

    const PAYMENT_DISABLED = 'payments-disabled';

    const IS_JOINED = 'is-joined';

    const SUCCESS = 'success';

    const BEFORE_EVENT = 'before_event';

    const AFTER_EVENT = 'after_event';

    public function add(ApiRequest $request, $id)
    {
        $params = $request->request->all();

        if (empty($params['targetId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $goods = $this->getGoodsService()->getGoods($id);
        if ('published' !== $goods['status']) {
            throw GoodsException::FORBIDDEN_JOIN_UNPUBLISHED_GOODS();
        }
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecs($params['targetId']);

        if ('published' !== $goodsSpecs['status']) {
            throw GoodsException::FORBIDDEN_JOIN_UNPUBLISHED_SPECS();
        }

        if ($this->needNoStudentNumTip($goods['type'], $goodsSpecs['targetId'])) {
            return ['success' => false, 'code' => self::NO_REMAIN];
        }

        if ($this->isMultiClassStudentNumLimit($goodsSpecs['targetId'])) {
            return ['success' => false, 'code' => self::NO_REMAIN];
        }

        if ($this->needUploadAvatar()) {
            return ['success' => false, 'code' => self::AVATAR_ALERT];
        }

        if ($this->needFillUserInfo()) {
            return ['success' => false, 'code' => self::FILL_USER_INFO];
        }

        if ($this->needOpenPayment($goodsSpecs)) {
            return ['success' => false, 'code' => self::PAYMENT_DISABLED];
        }

        if ($beforeEvent = $this->needInformationCollectionBeforeJoin($goods, $goodsSpecs)) {
            return ['success' => false, 'code' => self::BEFORE_EVENT, 'context' => $beforeEvent];
        }

        $this->tryFreeJoin($goods['type'], $goodsSpecs['targetId']);

        if ($this->isJoined($goods['type'], $goodsSpecs['targetId'])) {
            if ($afterEvent = $this->needInformationCollectionAfterJoin($goods, $goodsSpecs)) {
                return ['success' => false, 'code' => self::AFTER_EVENT, 'context' => $afterEvent];
            }

            return ['success' => false, 'code' => self::IS_JOINED];
        }

        return [
            'success' => true,
            'code' => self::SUCCESS,
        ];
    }

    protected function needNoStudentNumTip($type, $id)
    {
        if ('course' !== $type) {
            return false;
        }

        $course = $this->getCourseService()->getCourse($id);

        return $course['maxStudentNum'] > 0 && $course['maxStudentNum'] - $course['studentNum'] <= 0 && 'live' == $course['type'];
    }

    protected function isMultiClassStudentNumLimit($id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $multiClass = $this->getMultiClassService()->getMultiClassByCourseId($course['id']);

        if (!empty($multiClass['maxStudentNum'])) {
            return $multiClass['maxStudentNum'] - $course['studentNum'] <= 0;
        }

        return false;
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
            // $buyFields 可能为 NULL
            if (empty($buyFields)) {
                return false;
            }

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
        if ('course' === $type) {
            $this->getCourseService()->tryFreeJoin($id);
        } elseif ('classroom' === $type) {
            $this->getClassroomService()->tryFreeJoin($id);
        }
    }

    protected function isJoined($type, $id)
    {
        if ('course' === $type) {
            return $this->getCourseMemberService()->getCourseMember($id, $this->getCurrentUser()->getId());
        }

        if ('classroom' === $type) {
            return $this->getClassroomService()->isClassroomStudent($id, $this->getCurrentUser()->getId());
        }
    }

    protected function needInformationCollectionBeforeJoin($goods, $goodsSpecs)
    {
        $goodsEntity = $this->getGoodsService()->getGoodsEntityFactory()->create($goods['type']);
        $target = $goodsEntity->getTarget($goods);
        $canVipFreeJoin = $goodsEntity->canVipFreeJoin($goods, $goodsSpecs, $this->getCurrentUser()->getId());
        $goodsSpecs = $this->getGoodsService()->convertSpecsPrice($goods, $goodsSpecs);
        if ('0.00' == $goodsSpecs['price'] || $canVipFreeJoin) {
            $location = ['targetType' => $goods['type'], 'targetId' => $target['id']];
            $event = $this->getInformationCollectEventService()->getEventByActionAndLocation('buy_before', $location);
            if (empty($event)) {
                return [];
            }
            if ('classroom' === $goods['type']) {
                $goto = $this->generateUrl('classroom_buy', ['id' => $goodsSpecs['targetId']]);
            } elseif ('course' === $goods['type']) {
                $goto = $this->generateUrl('course_buy', ['id' => $goodsSpecs['targetId']]);
            }

            $url = $this->generateUrl('information_collect_event', [
                'eventId' => $event['id'],
                'goto' => empty($goto) ? '' : $goto,
            ]);

            return [
                'eventId' => $event['id'],
                'url' => $url,
            ];
        }
    }

    protected function needInformationCollectionAfterJoin($goods, $goodsSpecs)
    {
        $goodsEntity = $this->getGoodsService()->getGoodsEntityFactory()->create($goods['type']);
        $target = $goodsEntity->getTarget($goods);
        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation('buy_after', ['targetType' => $goods['type'], 'targetId' => $target['id']]);
        if (empty($event)) {
            return [];
        }

        if ('classroom' === $goods['type']) {
            $successUrl = $this->generateUrl('classroom_courses', ['classroomId' => $target['id']]);
        } elseif ('course' === $goods['type']) {
            $successUrl = $this->generateUrl('my_course_show', ['id' => $goodsSpecs['targetId']]);
        }
        $url = $this->generateUrl('information_collect_event', [
            'eventId' => $event['id'],
            'goto' => empty($successUrl) ? '' : $successUrl,
        ]);

        return ['eventId' => $event['id'], 'url' => $url];
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
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return EventService
     */
    protected function getInformationCollectEventService()
    {
        return $this->service('InformationCollect:EventService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }
}
