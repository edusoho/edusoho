<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolKit;
use Topxia\Common\NumberToolkit;
use Exception;

class ClassroomOrderProcessor extends BaseProcessor implements OrderProcessor
{
	protected $router = "classroom_show";

	public function getRouter() {
		return $this->router;
	}

    public function preCheck($targetId, $userId)
    {
        if ($this->getClassroomService()->isClassroomStudent($targetId, $userId)) {
            return array('error' => '已经是班级的学员了!');
        }

        return array();
    }

	public function getOrderInfo($targetId, $fields)
	{
        $classroom = $this->getClassroomService()->getClassroom($targetId);
        
        if(empty($classroom)) {
            throw new Exception("找不到要购买的班级!");
        }

        $courses = $this->getClassroomService()->findCoursesByClassroomId($targetId);
        $courseIds = $courses = ArrayToolkit::column($courses, "parentId");
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $users = array();
        foreach ($courses as $key => $course) {
            $users = array_merge($this->getUserService()->findUsersByIds($course['teacherIds']), $users);
        }
        $users = ArrayToolkit::index($users, "id");
        $headTeacher = $this->getUserService()->getUser($classroom["headTeacherId"]);

        list($coinEnable, $priceType, $cashRate) = $this->getCoinSetting();

        $courseIds = ArrayToolKit::column($courses, "id");

        $currentUser = $this->getUserService()->getCurrentUser();
        
        $classroomSetting = $this->getSettingService()->get("classroom");

        $paidCourses = array();
        if(!isset($classroomSetting["discount_buy"]) || $classroomSetting["discount_buy"] != 0) {
            $paidCourses = $this->getPaidCourses($currentUser, $courseIds);

            foreach ($paidCourses as $key => $paidCourse) {
                $afterDiscountPrice = $this->afterDiscountPrice($paidCourse, $priceType);
                if($afterDiscountPrice<=0){
                    unset($paidCourses[$key]);
                }else{
                    $paidCourses[$key]["afterDiscountPrice"] = $afterDiscountPrice;
                }
            }
        }

        $paidCoursesTotalPrice = $this->getCoursesTotalPrice($paidCourses, $priceType);

        if(!$coinEnable) {
            $totalPrice = $classroom["price"];
            $totalPrice = $totalPrice-$paidCoursesTotalPrice;
            $totalPrice = NumberToolkit::roundUp($totalPrice);

            if($totalPrice < 0){
                $totalPrice = 0;
            }

        	return array(
				'totalPrice' => $totalPrice,
				'targetId' => $targetId,
            	'targetType' => "classroom",

				'classroom' => empty($classroom) ? null : $classroom,
                'courses' => $courses,
                'paidCourses' => $paidCourses,
                'users' => $users,
                'headTeacher' => $headTeacher
        	);
        }

        $totalPrice = $classroom["price"];

        if ($priceType == "Coin") {
            $totalPrice = NumberToolkit::roundUp($totalPrice * $cashRate);
        }

        $totalPrice = $totalPrice - $paidCoursesTotalPrice;

        if($totalPrice < 0){
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
            'classroom' => empty($classroom) ? null : $classroom,
            'courses' => $courses,
            'paidCourses' => $paidCourses,
            'users' => $users,
            'headTeacher' => $headTeacher,
            
            'totalPrice' => $totalPrice,
            'targetId' => $targetId,
            'targetType' => "classroom",
            'cashRate' => $cashRate,
            'priceType' => $priceType,
            'account' => $account,
            'hasPayPassword' => $hasPayPassword,
            'coinPayAmount' => $coinPayAmount,
            'maxCoin' => $maxCoin,
        );
	}

    protected function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return '0%';
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100) . '%';

        return $percent;
    }

	public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields)
	{
        $totalPrice = 0;
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        if($priceType == "RMB") {
            $totalPrice = $classroom["price"];
        } else if ($priceType == "Coin") {
            $totalPrice = $classroom["price"] * $cashRate;
        }


        $totalPrice = (float)$totalPrice;
        $amount = $totalPrice;


        $courses = $this->getClassroomService()->findCoursesByClassroomId($targetId);
        if(empty($courses) || count($courses) == 0){
            throw new Exception("班级中还未设置课程，请联系管理员!");
        }

        $courseIds = $courses = ArrayToolkit::column($courses, "parentId");
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $coursesTotalPrice = $this->getCoursesTotalPrice($courses, $priceType);

        $courseIds = ArrayToolKit::column($courses, "id");

        $currentUser = $this->getUserService()->getCurrentUser();

        $classroomSetting = $this->getSettingService()->get("classroom");
        $paidCoursesTotalPrice = 0;
        $paidCourses = array();
        if(!isset($classroomSetting["discount_buy"]) || $classroomSetting["discount_buy"] != 0) {
            $paidCourses = $this->getPaidCourses($currentUser, $courseIds);
        }

        foreach ($paidCourses as $key => $paidCourse) {
            $afterDiscountPrice = $this->afterDiscountPrice($paidCourse, $priceType);
            $amount -= $afterDiscountPrice;
            $totalPrice -= $afterDiscountPrice;
        }

        $totalPrice = NumberToolkit::roundUp($totalPrice);


        if($totalPrice < 0){
            $totalPrice = 0;
        }

        if($amount<0.001){
            $amount=0;
        }

        //优惠码优惠价格
        $couponApp = $this->getAppService()->findInstallApp("Coupon");
        $couponSetting = $this->getSettingService()->get("coupon");
        if(!empty($couponApp) && isset($couponSetting["enabled"]) && $couponSetting["enabled"] == 1 && $fields["couponCode"] && trim($fields["couponCode"]) != "") {
            $couponResult = $this->afterCouponPay(
                $fields["couponCode"], 
                'classroom',
                $targetId, 
                $amount, 
                $priceType, 
                $cashRate
            );

            if(isset($couponResult["useable"]) && $couponResult["useable"]=="yes" && isset($couponResult["afterAmount"])){
                $amount = $couponResult["afterAmount"];
            }
        }

        //虚拟币优惠价格
        if(array_key_exists("coinPayAmount", $fields)) {
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
            $amount = $amount/$cashRate;
        }
        if($amount<0){
            $amount = 0;
        }
        $amount = NumberToolkit::roundUp($amount);
        return array(
        	$amount, 
        	$totalPrice, 
        	empty($couponResult) ? null : $couponResult,
        );
	}

    protected function getPaidCourses($currentUser, $courseIds){
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
            return ;
        }

        $this->getClassroomOrderService()->doSuccessPayOrder($order['id']);

        return ;
    }

    protected function afterDiscountPrice($course, $priceType)
    {
        $coursePrice = 0;
        if($priceType == "RMB") {
            $coursePrice = $course["originPrice"];
        } else if($priceType == "Coin") {
            $coursePrice = $course["originCoinPrice"];
        }
        return $coursePrice;
    }

    protected function getCoursesTotalPrice($courses, $priceType)
    {
        $coursesTotalPrice = 0;
        foreach ($courses as $key => $course) {
            if($priceType == "RMB") {
                $coursesTotalPrice += $course["originPrice"];
            } else if($priceType == "Coin") {
                $coursesTotalPrice += $course["originCoinPrice"];
            }
        }
        return $coursesTotalPrice;
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

	protected function getClassroomOrderService() {
		return ServiceKernel::instance()->createService("Classroom:Classroom.ClassroomOrderService");
	}
}