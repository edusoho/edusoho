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
        $paidCoursesTotalPrice = 0;
        $paidCourses = array();
        if(!isset($classroomSetting["discount_buy"]) || $classroomSetting["discount_buy"] != 0) {
            $courseMembers = $this->getCourseService()->findCoursesByStudentIdAndCourseIds($currentUser->id, $courseIds);
            $courseMembers = ArrayToolkit::index($courseMembers, "courseId");

            $courseIds = ArrayToolkit::column($courseMembers, "courseId");
            $paidCourses = $this->getCourseService()->findCoursesByIds($courseIds);

            foreach ($paidCourses as $key => $paidCourse) {
                $paidCourses[$key]["percent"] = $this->calculateUserLearnProgress($paidCourse, $courseMembers[$paidCourse["id"]]);
                $paidCourses[$key]["deadline"] = $courseMembers[$paidCourse["id"]]["deadline"];
                $paidCourses[$key]["deadlineDate"] = date('Y-m-d H:i', $courseMembers[$paidCourse["id"]]["deadline"]*1000);
            }
            $paidCoursesTotalPrice = $this->getCoursesTotalPrice($paidCourses, $priceType);
        }


        $coursesTotalPrice = $this->getCoursesTotalPrice($courses, $priceType);

        if(!$coinEnable) {
            $totalPrice = $classroom["price"];
            $discountRate = 0;
            if($coursesTotalPrice>0)
                $discountRate = $totalPrice/$coursesTotalPrice;

            foreach ($paidCourses as $key => $paidCourse) {
                $paidCourses[$key]["afterDiscountPrice"] = $this->afterDiscountPrice($paidCourse, $priceType, $discountRate);
                if($paidCourses[$key]["afterDiscountPrice"]>0) {
                    $totalPrice -= $paidCourses[$key]["afterDiscountPrice"];
                } else {
                    unset($paidCourses[$key]);
                }
            }

            $totalPrice = NumberToolkit::roundUp($totalPrice);

            if($totalPrice < 0){
                $totalPrice = 0;
            }

        	return array(
				'totalPrice' => $totalPrice,
				'targetId' => $targetId,
            	'targetType' => "classroom",
                'coursesTotalPrice' => $coursesTotalPrice,
                'paidCoursesTotalPrice' => $paidCoursesTotalPrice,
                'discountRate' => $discountRate,

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

        $afterCourseDiscountPrice = $totalPrice;
        $discountRate = 0;
        if($coursesTotalPrice>0)
            $discountRate = $totalPrice/$coursesTotalPrice;

        foreach ($paidCourses as $key => $paidCourse) {
            $afterDiscountPrice = $this->afterDiscountPrice($paidCourse, $priceType, $discountRate);
            $paidCourses[$key]["afterDiscountPrice"] = $afterDiscountPrice;
            if ($paidCourses[$key]["afterDiscountPrice"] > 0) {
                $afterCourseDiscountPrice -= $paidCourses[$key]["afterDiscountPrice"];
                $totalPrice -= $paidCourses[$key]["afterDiscountPrice"];
            } else {
                unset($paidCourses[$key]);
            }
        }

        $totalPrice = NumberToolkit::roundUp($totalPrice);

        if($totalPrice < 0){
            $totalPrice = 0;
        }

        if($afterCourseDiscountPrice<0){
            $afterCourseDiscountPrice=0;
        }

        list($afterCourseDiscountPrice, $coinPayAmount, $account, $hasPayPassword) = $this->calculateCoinAmount($afterCourseDiscountPrice, $priceType, $cashRate);

        return array(
            'classroom' => empty($classroom) ? null : $classroom,
            'courses' => $courses,
            'paidCourses' => $paidCourses,
            'users' => $users,
            'headTeacher' => $headTeacher,
            'coursesTotalPrice' => $coursesTotalPrice,
            'paidCoursesTotalPrice' => $paidCoursesTotalPrice,
            'discountRate' => $discountRate,
            
            'totalPrice' => $totalPrice,
            'targetId' => $targetId,
            'targetType' => "classroom",
            'cashRate' => $cashRate,
            'priceType' => $priceType,
            'account' => $account,
            'hasPayPassword' => $hasPayPassword,
            'coinPayAmount' => $coinPayAmount,
        );
	}

    private function calculateUserLearnProgress($course, $member)
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
        $coursesTotalPrice = $this->getCoursesTotalPrice($courses, $priceType);

        $courseIds = ArrayToolKit::column($courses, "id");

        $currentUser = $this->getUserService()->getCurrentUser();

        $classroomSetting = $this->getSettingService()->get("classroom");
        $paidCoursesTotalPrice = 0;
        $paidCourses = array();
        if(!isset($classroomSetting["discount_buy"]) || $classroomSetting["discount_buy"] != 0) {
            $courseMembers = $this->getCourseService()->findCoursesByStudentIdAndCourseIds($currentUser->id, $courseIds);
            $courseMembers = ArrayToolkit::index($courseMembers, "courseId");

            $paidCourseIds = ArrayToolkit::column($courseMembers, "courseId");
            $paidCourses = $this->getCourseService()->findCoursesByIds($paidCourseIds);
        }

        if($coursesTotalPrice>0){
            $discountRate = $totalPrice/$coursesTotalPrice;
        } else {
            $discountRate = 1;
        }

        foreach ($paidCourses as $key => $paidCourse) {
            $afterDiscountPrice = $this->afterDiscountPrice($paidCourse, $priceType, $discountRate);
            $amount -= $afterDiscountPrice;
            $totalPrice -= $afterDiscountPrice;
        }

        $totalPrice = NumberToolkit::roundUp($totalPrice);

        if(intval($totalPrice*100) != intval($fields['totalPrice']*100)) {
            throw new Exception("实际价格不匹配，不能创建订单!");
        }

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

    private function afterDiscountPrice($course, $priceType, $discountRate)
    {
        $coursePrice = 0;
        if($priceType == "RMB") {
            $coursePrice = $course["price"];
        } else if($priceType == "Coin") {
            $coursePrice = $course["coinPrice"];
        }
        return floor(((float)$coursePrice)*$discountRate*100)/100;
    }

    private function getCoursesTotalPrice($courses, $priceType)
    {
        $coursesTotalPrice = 0;
        foreach ($courses as $key => $course) {
            if($priceType == "RMB") {
                $coursesTotalPrice += $course["price"];
            } else if($priceType == "Coin") {
                $coursesTotalPrice += $course["coinPrice"];
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