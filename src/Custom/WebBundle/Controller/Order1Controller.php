<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\OrderController;
use Symfony\Component\HttpFoundation\Request;

class Order1Controller extends OrderController
{
    public function couponCheckAction (Request $request, $type, $id)
    {   
        $user=$this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $code = $request->request->get('code');

            //判断coupon是否合法，是否存在跟是否过期跟是否可用于当前课程
            $course = $this->getCourseService()->getCourse($id);

            $couponInfo = $this->getCouponService()->checkCouponUseable($code, $type, $id, $course['price']);
            
            $vip=$this->getVipService()->getMemberByUserId($user->id);
            $level=array();

            if($vip){

            $level=$this->getLevelService()->getLevel($vip['levelId']);

            if($level){
                
                $amount=$couponInfo['afterAmount'];
                $couponInfo['afterAmount']=$couponInfo['afterAmount']*0.1*$level['courseDiscount'];

                $couponInfo['afterAmount']=sprintf("%.2f", $couponInfo['afterAmount']);

                $cut=$amount-$couponInfo['afterAmount'];

                $couponInfo['decreaseAmount']=$couponInfo['decreaseAmount']+$cut;

            }
        }
            
            return $this->createJsonResponse($couponInfo);
        }
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    } 

    protected function getLevelService()
    {   
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }
}