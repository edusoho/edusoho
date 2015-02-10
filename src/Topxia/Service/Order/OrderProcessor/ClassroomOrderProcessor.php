<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolKit;
use Topxia\Common\NumberToolkit;
use Exception;

class ClassroomOrderProcessor extends BaseProcessor implements OrderProcessor
{
	protected $router = "classroom_manage";

	public function getRouter() {
		return $this->router;
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
        $headerTeacher = $this->getUserService()->getUser($classroom["headerTeacherId"]);

        list($coinEnable, $priceType, $cashRate) = $this->getCoinSetting();

        $courseIds = ArrayToolKit::column($courses, "id");

        $currentUser = $this->getUserService()->getCurrentUser();
        $paidCourses = $this->getCourseService()->findCoursesByStudentIdAndCourseIds($currentUser->id, $courseIds);

        $coursesTotalPrice = $this->getCoursesTotalPrice($courses, "RMB");
        $paidCoursesTotalPrice = $this->getCoursesTotalPrice($paidCourses, "RMB");

        if(!$coinEnable) {
            $totalPrice = $classroom["price"];

            $discountRate = $totalPrice/$coursesTotalPrice;

            foreach ($paidCourses as $key => $paidCourse) {
                $paidCourses[$key]["afterDiscountPrice"] = $this->afterDiscountPrice($paidCourse, $priceType, $discountRate);
            }

        	return array(
				'totalPrice' => $totalPrice,
				'targetId' => $targetId,
            	'targetType' => "classroom",
                'paidCourses' => $paidCourses,
                'coursesTotalPrice' => $coursesTotalPrice,
                'paidCoursesTotalPrice' => $paidCoursesTotalPrice,
                'discountRate' => $discountRate,

				'classroom' => empty($classroom) ? null : $classroom,
                'courses' => $courses,
                'paidCourses' => $paidCourses,
                'users' => $users,
                'headerTeacher' => $headerTeacher
        	);
        }

        $totalPrice = $classroom["price"];

        list($totalPrice, $coinPayAmount, $account, $hasPayPassword) = $this->calculateCoinAmount($totalPrice, $priceType, $cashRate);

        $discountRate = $totalPrice/$coursesTotalPrice;
        foreach ($paidCourses as $key => $paidCourse) {
            $paidCourses[$key]["afterDiscountPrice"] = $this->afterDiscountPrice($paidCourse, $priceType, $discountRate);
        }
        
        return array(
            'classroom' => empty($classroom) ? null : $classroom,
            'courses' => $courses,
            'paidCourses' => $paidCourses,
            'users' => $users,
            'headerTeacher' => $headerTeacher,
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

	public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields)
	{
        $totalPrice = 0;
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        if($priceType == "RMB") {
            $totalPrice = $classroom["price"];
        } else if ($priceType == "Coin") {
            $totalPrice = $classroom["price"] * $cashRate;
        }

        if($totalPrice != $fields['totalPrice']) {
            throw new Exception("实际价格不匹配，不能创建订单!");
        }

        $totalPrice = (float)$totalPrice;
        $amount = $totalPrice;

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
            $coursePrice += $course["price"];
        } else if($priceType == "Coin") {
            $coursePrice += $course["coinPrice"];
        }
        return $coursePrice*$discountRate;
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
		return ServiceKernel::instance()->createService("Classroom.ClassroomOrderService");
	}
}