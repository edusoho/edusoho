<?php

namespace Biz\S2B2C\Event;

use Biz\Course\Service\CourseService;
use Biz\OrderFacade\Service\OrderRefundService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductReportService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Psr\Log\LoggerInterface;
use QiQiuYun\SDK\Service\S2B2CService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CourseProductSubscriber
 *
 * @codeCoverageIgnore
 * 需要通知远程的接口，忽略测试
 */
class CourseProductSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.join' => 'onCourseJoin',
            'order.refunded' => 'onOrderRefunded',
            'course.marketing.update' => 'onCourseMarketingUpdate',
        ];
    }

    public function onCourseMarketingUpdate(Event $event)
    {
        $courses = $event->getSubject();
        $course = $courses['newCourse'];
        if ($this->isSupplierCourse($course)) {
            $courseProduct = $this->getS2b2cProductService()->getByTypeAndLocalResourceId('course', $course['id']);
            $this->getS2B2CService()->changeProductSellingPrice($courseProduct['s2b2cProductDetailId'], $course['price']);
        }
    }

    public function onCourseJoin(Event $event)
    {
        try {
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            $course = $event->getSubject();
            if (empty($s2b2cConfig['supplierId']) || 'self' == $course['platform']) {
                $this->getLogger()->info('[onCourseJoin] s2b2c config not enabled or course not belong supplier, no need to report');

                return;
            }

            $s2b2cProduct = $this->getS2b2cProductService()->getByTypeAndLocalResourceId('course', $course['id']);
            $member = $event->getArgument('member');
            $report = $this->getProductReportService()->create([
                's2b2cProductId' => $s2b2cProduct['id'],
                'userId' => $member['userId'],
                'type' => ProductReportService::TYPE_JOIN_COURSE,
                'orderId' => $member['orderId'],
            ]);
            $this->getLogger()->info(sprintf('[onCourseJoin] report record #%s created', $report['id']));

            $order = $this->getOrderService()->getOrder($member['orderId']);
            $user = $this->getUserService()->getUser($member['userId']);
            $params = [
                'merchantOrderId' => $member['orderId'],
                'merchantOrderRefundDays' => empty($order['expired_refund_days']) ? 0 : $order['expired_refund_days'],
                'merchantOrderUserNickname' => $user['nickname'],
                'productDetailId' => $s2b2cProduct['s2b2cProductDetailId'],
                'merchantReportId' => $report['id'],
            ];

            $this->getProductReportService()->updateStatusToSent($report['id']);
            $this->getLogger()->info(sprintf('[onCourseJoin] change report record #%s status to sent', $report['id']));

            $result = $this->getS2B2CService()->reportSuccessOrder($params);
            if (isset($result['error'])) {
                $this->getProductReportService()->updateFailedReason($report['id'], $result['error']);
                $this->getLogger()->info(sprintf('[onCourseJoin] report failed reportId:%s error:%s', $report['id'], $result['error']));
            } else {
                $this->getProductReportService()->updateStatusToSucceed($report['id']);
                $this->getLogger()->info(sprintf('[onCourseJoin] change report record #%s status to succeed', $report['id']));
            }
            $this->getLogger()->info('[onCourseJoin] report finished');
        } catch (\Throwable $e) {
            $this->getLogger()->error(sprintf('[onCourseJoin] report failed: %s %s', $e->getMessage(), $e->getTraceAsString()));
        }
    }

    public function onOrderRefunded(Event $event)
    {
        try {
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            if (empty($s2b2cConfig['supplierId'])) {
                $this->getLogger()->info('[onOrderRefunded] s2b2c config not enabled, no need to report');

                return;
            }

            $order = $event->getSubject();
            $target = $order['items'][0];
            if ('course' != $target['target_type']) {
                $this->getLogger()->info('[onOrderRefunded] target type not support, no need to report');

                return;
            }

            $course = $this->getCourseService()->getCourse($target['target_id']);
            if ('self' == $course['platform']) {
                $this->getLogger()->info("[onOrderRefunded] course #{$target['target_id']} not supplier course, no need to report");

                return;
            }

            $joinReport = $this->getProductReportService()->getByOrderIdAndType($order['id'], ProductReportService::TYPE_JOIN_COURSE);
            if (empty($joinReport)) {
                $this->getLogger()->error(sprintf('[onOrderRefunded] product report orderId #%s not found', $order['id']));

                return;
            }

            $s2b2cProduct = $this->getS2b2cProductService()->getByTypeAndLocalResourceId('course', $target['target_id']);
            if (empty($s2b2cProduct)) {
                $this->getLogger()->error(sprintf('[onOrderRefunded] merchant product #%s not found', $target['target_id']));

                return;
            }

            $refundReport = $this->getProductReportService()->create([
                's2b2cProductId' => $s2b2cProduct['id'],
                'userId' => $order['user_id'],
                'type' => ProductReportService::TYPE_REFUND,
                'orderId' => $order['id'],
            ]);

            $params = [
                'productDetailId' => $s2b2cProduct['s2b2cProductDetailId'],
                'merchantRefundReportId' => $refundReport['id'],
                'merchantOrderId' => $order['id'],
                'merchantLastReportId' => $joinReport['id'],
            ];
            $result = $this->getS2B2CService()->reportRefundOrder($params);
            if (isset($result['error'])) {
                $this->getProductReportService()->updateFailedReason($refundReport['id'], $result['error']);
                $this->getLogger()->info(sprintf('[onOrderRefunded] report failed reportId:%s error:%s', $refundReport['id'], $result['error']));
            } else {
                $this->getProductReportService()->updateStatusToSucceed($refundReport['id']);
                $this->getLogger()->info(sprintf('[onOrderRefunded] change report record #%s status to succeed', $refundReport['id']));
            }

            $this->getLogger()->info('[onOrderRefunded] report finished');
        } catch (\Throwable $e) {
            $this->getLogger()->error(sprintf('[onOrderRefunded] report failed: %s %s', $e->getMessage(), $e->getTraceAsString()));
        }
    }

    protected function setUserInfo(&$order)
    {
        $user = $this->getUserService()->getUser($order['user_id']);
        $order['nickname'] = $user['nickname'];
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return OrderRefundService
     */
    protected function getOrderRefundService()
    {
        return $this->getBiz()->service('OrderFacade:OrderRefundService');
    }

    /**
     * @return \Codeages\Biz\Order\Service\OrderRefundService
     */
    protected function getBaseOrderRefundService()
    {
        return $this->getBiz()->service('Order:OrderRefundService');
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->getBiz()->offsetGet('s2b2c.merchant.logger');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function isSupplierCourse($course)
    {
        return 'supplier' === $course['platform'];
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->getBiz()->service('S2B2C:CourseProductService');
    }

    /**
     * @return S2B2CService
     */
    protected function getS2B2CService()
    {
        return $this->getS2B2CFacadeService()->getS2B2CService();
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->getBiz()->service('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return ProductService
     */
    protected function getS2b2cProductService()
    {
        return $this->getBiz()->service('S2B2C:ProductService');
    }

    /**
     * @return ProductReportService
     */
    protected function getProductReportService()
    {
        return $this->getBiz()->service('S2B2C:ProductReportService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }
}
