<?php
namespace Topxia\Service\Order\OrderProcessor;

use Exception;
use Topxia\Common\ArrayToolKit;
use Topxia\Common\NumberToolkit;
use Topxia\Service\Common\ServiceKernel;

class ClassroomOrderProcessor extends BaseProcessor implements OrderProcessor
{
    public function preCheck($targetId, $userId)
    {
        if ($this->getClassroomService()->isClassroomStudent($targetId, $userId)) {
            return array('error' => '已经是班级的学员了!');
        }

        $classroom = $this->getClassroomService()->getClassroom($targetId);

        if (!$classroom['buyable']) {
            return array('error' => '该班级不可购买，如有需要，请联系客服');
        }

        if ($classroom['status'] != 'published') {
            return array('error' => '不能加入未发布班级!');
        }

        if (!$classroom['buyable']) {
            $classroomSetting = $this->getSettingService()->get('classroom');
            return array('error' => "该{$classroomSetting['name']}不可购买，如有需要，请联系客服");
        }

        return array();
    }

    public function getOrderInfo($targetId, $fields)
    {
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        if (empty($classroom)) {
            throw new Exception("找不到要购买的班级!");
        }

        $courses   = $this->getClassroomService()->findCoursesByClassroomId($targetId);
        $courseIds = $courses = ArrayToolkit::column($courses, "parentId");
        $courses   = $this->getCourseService()->findCoursesByIds($courseIds);

        $users = array();

        foreach ($courses as $key => $course) {
            $users = array_merge($this->getUserService()->findUsersByIds($course['teacherIds']), $users);
        }

        $users       = ArrayToolkit::index($users, "id");
        $headTeacher = $this->getUserService()->getUser($classroom["headTeacherId"]);

        list($coinEnable, $priceType, $cashRate) = $this->getCoinSetting();

        $courseIds = ArrayToolKit::column($courses, "id");

        $currentUser = $this->getUserService()->getCurrentUser();

        $classroomSetting = $this->getSettingService()->get("classroom");

        $paidCourses = array();

        if (!isset($classroomSetting["discount_buy"]) || $classroomSetting["discount_buy"] != 0) {
            $paidCourses = $this->getPaidCourses($currentUser, $courseIds);

            foreach ($paidCourses as $key => $paidCourse) {
                $afterDiscountPrice = $this->afterDiscountPrice($paidCourse, $priceType);

                if ($afterDiscountPrice <= 0) {
                    unset($paidCourses[$key]);
                } else {
                    $paidCourses[$key]["afterDiscountPrice"] = $afterDiscountPrice;
                }
            }
        }

        $paidCoursesTotalPrice = $this->getCoursesTotalPrice($paidCourses, $priceType);

        if (!$coinEnable) {
            $totalPrice = $classroom["price"];
            $totalPrice = $totalPrice - $paidCoursesTotalPrice;
            $totalPrice = NumberToolkit::roundUp($totalPrice);

            if ($totalPrice < 0) {
                $totalPrice = 0;
            }

            return array(
                'totalPrice'  => $totalPrice,
                'targetId'    => $targetId,
                'targetType'  => "classroom",

                'classroom'   => empty($classroom) ? null : $classroom,
                'courses'     => $courses,
                'paidCourses' => $paidCourses,
                'users'       => $users,
                'headTeacher' => $headTeacher
            );
        }

        $totalPrice = $classroom["price"];

        if ($priceType == "Coin") {
            $totalPrice = NumberToolkit::roundUp($totalPrice * $cashRate);
        }

        $totalPrice = $totalPrice - $paidCoursesTotalPrice;

        if ($totalPrice < 0) {
            $totalPrice = 0;
        }

        list($totalPrice, $coinPayAmount, $account, $hasPayPassword) = $this->calculateCoinAmount($totalPrice, $priceType, $cashRate);

        if ($priceType == "Coin") {
            $maxCoin = $coinPayAmount;
        } else {
            $maxCoin = NumberToolkit::roundUp($classroom['price'] * $classroom['maxRate'] / 100 * $cashRate);
        }

        $totalPrice = NumberToolkit::roundUp($totalPrice);

        return array(
            'classroom'      => empty($classroom) ? null : $classroom,
            'courses'        => $courses,
            'paidCourses'    => $paidCourses,
            'users'          => $users,
            'headTeacher'    => $headTeacher,

            'totalPrice'     => $totalPrice,
            'targetId'       => $targetId,
            'targetType'     => "classroom",
            'cashRate'       => $cashRate,
            'priceType'      => $priceType,
            'account'        => $account,
            'hasPayPassword' => $hasPayPassword,
            'coinPayAmount'  => $coinPayAmount,
            'maxCoin'        => $maxCoin
        );
    }

    protected function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return '0%';
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100).'%';

        return $percent;
    }

    public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields)
    {
        $totalPrice = 0;
        $classroom  = $this->getClassroomService()->getClassroom($targetId);

        if ($priceType == "RMB") {
            $totalPrice = $classroom["price"];
        } elseif ($priceType == "Coin") {
            $totalPrice = $classroom["price"] * $cashRate;
        }

        $totalPrice = (float) $totalPrice;
        $amount     = $totalPrice;

        $courses = $this->getClassroomService()->findCoursesByClassroomId($targetId);

        if (empty($courses) || count($courses) == 0) {
            throw new Exception("班级中还未设置课程，请联系管理员!");
        }

        $courseIds = $courses = ArrayToolkit::column($courses, "parentId");
        $courses   = $this->getCourseService()->findCoursesByIds($courseIds);

        $coursesTotalPrice = $this->getCoursesTotalPrice($courses, $priceType);

        $courseIds = ArrayToolKit::column($courses, "id");

        $currentUser = $this->getUserService()->getCurrentUser();

        $classroomSetting      = $this->getSettingService()->get("classroom");
        $paidCoursesTotalPrice = 0;
        $paidCourses           = array();

        if (!isset($classroomSetting["discount_buy"]) || $classroomSetting["discount_buy"] != 0) {
            $paidCourses = $this->getPaidCourses($currentUser, $courseIds);
        }

        foreach ($paidCourses as $key => $paidCourse) {
            $afterDiscountPrice = $this->afterDiscountPrice($paidCourse, $priceType);
            $amount -= $afterDiscountPrice;
            $totalPrice -= $afterDiscountPrice;
        }

        $totalPrice = NumberToolkit::roundUp($totalPrice);

        if ($totalPrice < 0) {
            $totalPrice = 0;
        }

        if ($amount < 0.001) {
            $amount = 0;
        }

        //优惠码优惠价格

        if (!empty($fields["couponCode"]) && trim($fields["couponCode"]) != "") {
            $couponResult = $this->afterCouponPay(
                $fields["couponCode"],
                'classroom',
                $targetId,
                $amount,
                $priceType,
                $cashRate
            );

            if (isset($couponResult["useable"]) && $couponResult["useable"] == "yes" && isset($couponResult["afterAmount"])) {
                $amount = $couponResult["afterAmount"];
            }
        }

        //虚拟币优惠价格

        if (array_key_exists("coinPayAmount", $fields)) {
            $amount = $this->afterCoinPay(
                $coinEnabled,
                $priceType,
                $cashRate,
                $amount,
                $fields['coinPayAmount'],
                $fields["payPassword"]
            );
        }

        if ($priceType == "Coin") {
            $amount = $amount / $cashRate;
        }

        if ($amount < 0) {
            $amount = 0;
        }

        $amount = NumberToolkit::roundUp($amount);
        return array(
            $amount,
            $totalPrice,
            empty($couponResult) ? null : $couponResult
        );
    }

    protected function getPaidCourses($currentUser, $courseIds)
    {
        $courseMembers = $this->getCourseService()->findCoursesByStudentIdAndCourseIds($currentUser->id, $courseIds);
        $courseMembers = ArrayToolkit::index($courseMembers, "courseId");
        $paidCourseIds = ArrayToolkit::column($courseMembers, "courseId");
        return $this->getCourseService()->findCoursesByIds($paidCourseIds);
    }

    public function createOrder($orderInfo, $fields)
    {
        return $this->getClassroomOrderService()->createOrder($orderInfo);
    }

    public function doPaySuccess($success, $order)
    {
        if (!$success) {
            return;
        }

        $this->getClassroomOrderService()->doSuccessPayOrder($order['id']);

        return;
    }

    protected function afterDiscountPrice($course, $priceType)
    {
        $coursePrice = 0;

        if ($priceType == "RMB") {
            $coursePrice = $course["originPrice"];
        } elseif ($priceType == "Coin") {
            $coursePrice = $course["originPrice"];
        }

        return $coursePrice;
    }

    protected function getCoursesTotalPrice($courses, $priceType)
    {
        $coursesTotalPrice = 0;

        foreach ($courses as $key => $course) {
            if ($priceType == "RMB") {
                $coursesTotalPrice += $course["originPrice"];
            } elseif ($priceType == "Coin") {
                $coursesTotalPrice += $course["originPrice"];
            }
        }

        return $coursesTotalPrice;
    }

    public function getOrderBySn($sn)
    {
        return $this->getOrderService()->getOrderBySn($sn);
    }

    public function updateOrder($id, $fileds)
    {
        return $this->getOrderService()->updateOrder($id, $fileds);
    }

    public function getNote($targetId)
    {
        $classroom = $this->getClassroomService()->getClassroom($targetId);
        return str_replace(' ', '', strip_tags($classroom['about']));
    }

    public function getTitle($targetId)
    {
        $classroom = $this->getClassroomService()->getClassroom($targetId);
        return str_replace(' ', '', strip_tags($classroom['title']));
    }

    public function pay($payData)
    {
        return $this->getPayCenterService()->pay($payData);
    }

    public function callbackUrl($order, $container)
    {
        $goto = $container->get('router')->generate('classroom_show', array('id' => $order["targetId"]), true);
        return $goto;
    }

    public function cancelOrder($id, $message, $data)
    {
        return $this->getOrderService()->cancelOrder($id, $message, $data);
    }

    public function createPayRecord($id, $payData)
    {
        return $this->getOrderService()->createPayRecord($id, $payData);
    }

    public function generateOrderToken()
    {
        return 'c'.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    public function getOrderInfoTemplate()
    {
        return "ClassroomBundle:Classroom:orderInfo";
    }

    public function isTargetExist($targetId)
    {
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        if (empty($classroom) || $classroom['status'] == 'closed') {
            return false;
        }

        return true;
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getCashAccountService()
    {
        return ServiceKernel::instance()->createService('Cash.CashAccountService');
    }

    protected function getClassroomOrderService()
    {
        return ServiceKernel::instance()->createService("Classroom:Classroom.ClassroomOrderService");
    }

    protected function getOrderService()
    {
        return ServiceKernel::instance()->createService('Order.OrderService');
    }

    protected function getPayCenterService()
    {
        return ServiceKernel::instance()->createService('PayCenter.PayCenterService');
    }
}
