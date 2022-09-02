<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class CourseProduct extends BaseGoodsProduct
{
    const TYPE = 'course';

    public $showTemplate = 'order/show/course-item.html.twig';

    public $targetType = self::TYPE;

    public $goods;

    public $goodsSpecs;

    public $originalTargetId;

    public $courseSet;

    /**
     * 课程展示价格
     *
     * @var float
     */
    public $price;

    public function init(array $params)
    {
        //获取核心商品以及规格资源
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecs($params['targetId']);
        $this->goodsSpecs = $goodsSpecs;
        $goods = $this->getGoodsService()->getGoods($goodsSpecs['goodsId']);
        $this->goods = $goods;
        $this->goodsId = $goods['id'];

        //声明购买目标ID，商品剥离改造之前targetId是计划ID,现在增加了goodsSpecsId
        $this->goodsSpecsId = $params['targetId'];

        $this->targetId = $goodsSpecs['targetId'];

        //originalTargetId 兼容老数据，保存的是改造之前的计划ID,逐步去除targetId
        $this->originalTargetId = $goodsSpecs['targetId'];

        //对应具体课程以及计划资源，兼容老数据
        $course = $this->getCourseService()->getCourse($this->originalTargetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $this->courseSet = $courseSet;

        //供PC端返回商品页面用，商品剥离改造之前是课程概览页，现在是商品页
        $this->backUrl = ['routing' => 'goods_show', 'params' => ['id' => $goodsSpecs['goodsId'], 'targetId' => $goodsSpecs['targetId']]];

        //供支付成功后页面的跳转链接，改造前和改造后保持一致
        $this->successUrl = $this->getSuccessUrl();

        //默认计划的标题在课程里面如果没有第二个计划是空的，商品规格这边如果没有计划标题就直接换成了课程标题，所以做如下处理
        $this->title = empty($goodsSpecs['title']) ? $goods['title'] : $goods['title'] . '-' . $goodsSpecs['title'];
        if (empty($this->title) && isset($params['orderItemId'])) {
            $orderItem = $this->getOrderService()->getOrderItem($params['orderItemId']);
            $this->title = $orderItem['title'];
        }

        $this->cover = empty($goodsSpecs['images']) ? $goods['images'] : $goodsSpecs['images'];

        //改造之前是课程和计划都发布才能购买。现在是商品和规格都发布才是可购买
        $this->productEnable = 'published' === $goods['status'] && 'published' === $goodsSpecs['status'];

        //由于商品的原价的price,不会随着打折活动等变换价格，而计划的价格是打折后的价格，所以我们这里显示价格采用的是打折后的价格
        $this->price = $course['price']; //兼容性
        //原价来自于课程的价格改动后，课程的原价要保持一致 @todo price 会同步，所以这里要验证一下价格的来源
        $this->originPrice = $goodsSpecs['price'];

        //最大折扣，改造之前是课程可以设置的最大折扣比率
        $this->maxRate = $goods['maxRate'];
    }

    public function validate()
    {
        $access = $this->getCourseService()->canJoinCourse($this->goodsSpecs['targetId']);

        $course = $this->getCourseService()->getCourse($this->goodsSpecs['targetId']);

        if (!$course['buyable']) {
            throw OrderPayCheckException::UNPURCHASABLE_PRODUCT();
        }

        if (AccessorInterface::SUCCESS !== $access['code']) {
            throw OrderPayCheckException::UNPURCHASABLE_PRODUCT();
        }
    }

    public function onPaid($orderItem)
    {
        $targetName = '课程';
        $this->smsCallback($orderItem, $targetName);

        $order = $this->getOrderService()->getOrder($orderItem['order_id']);
        $info = [
            'orderId' => $order['id'],
            'remark' => $order['created_reason'],
        ];

        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);

        try {
            if (!$this->getCourseMemberService()->isCourseStudent($course['id'], $orderItem['user_id'])) {
                $member = $this->getCourseMemberService()->becomeStudent($course['id'], $orderItem['user_id'], $info);
            }

            if (isset($member)) {
                $this->getLogService()->info('course', 'join_course', "加入教学计划《{$course['title']}》", ['userId' => $orderItem['user_id'], 'courseId' => $course['id'], 'title' => ($course['title']) ? $course['title'] : $course['courseSetTitle']]);
            }

            return OrderStatusCallback::SUCCESS;
        } catch (\Exception $e) {
            $this->getLogService()->error('order', 'course_callback', 'order.course_callback.fail',
                ['error' => $e->getMessage(), 'context' => $orderItem]);

            return false;
        }
    }

    public function onOrderRefundAuditing($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);

        $this->getCourseMemberService()->lockStudent($course['id'], $orderItem['user_id']);
    }

    public function onOrderRefundCancel($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);

        $this->getCourseMemberService()->unlockStudent($course['id'], $orderItem['user_id']);
    }

    public function onOrderRefundRefunded($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);
        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $orderItem['user_id']);
        if (!empty($member)) {
            $info = [
                'reason' => $orderRefundItem['order_refund']['reason'],
                'reason_type' => 'refund',
            ];
            $this->getCourseMemberService()->removeStudent($course['id'], $orderItem['user_id'], ['reason'=>'同意退款','reason_type'=>'exit']);
        }
        $this->updateMemberRecordByRefundItem($orderItem);
    }

    public function onOrderRefundRefused($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $course = $this->getCourseByGoodsSpecsId($orderItem['target_id']);

        if (!empty($course)) {
            $this->getCourseMemberService()->unlockStudent($course['id'], $orderItem['user_id']);
        }
    }

    protected function getSuccessUrl()
    {
        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation('buy_after', [
            'targetType' => $this->targetType,
            'targetId' => $this->courseSet['id'],
        ]);

        if (empty($event)) {
            return ['my_course_show', ['id' => $this->targetId]];
        }

        return ['information_collect_event', ['eventId' => $event['id'], 'goto' => $this->generateUrl('my_course_show', ['id' => $this->targetId])]];
    }

    protected function getCourseByGoodsSpecsId($id)
    {
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecs($id);

        return $this->getCourseService()->getCourse($goodsSpecs['targetId']);
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    protected function getInformationCollectEventService()
    {
        return $this->biz->service('InformationCollect:EventService');
    }
}
