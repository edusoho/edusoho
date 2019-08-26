<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CouponController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $default = array(
            'batchIdNotEqual' => 0,
        );
        $conditions = array_merge($default, $conditions);

        $paginator = new Paginator(
            $request,
            $this->getCouponService()->searchCouponsCount($conditions),
            20
        );

        $coupons = $this->getCouponService()->searchCoupons(
            $conditions,
            array('orderTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $batchs = $this->getCouponBatchService()->findBatchsByIds(ArrayToolkit::column($coupons, 'batchId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($coupons, 'userId'));
        $orders = $this->getOrderService()->findOrdersByIds(ArrayToolkit::column($coupons, 'orderId'));

        return $this->render('admin/coupon/query.html.twig', array(
            'coupons' => $coupons,
            'paginator' => $paginator,
            'batchs' => $batchs,
            'users' => $users,
            'orders' => ArrayToolkit::index($orders, 'id'),
        ));
    }

    public function settingAction(Request $request)
    {
        $couponSetting = $this->getSettingService()->get('coupon', array());

        $default = array(
            'enabled' => 1,
        );

        $couponSetting = array_merge($default, $couponSetting);

        if ('POST' == $request->getMethod()) {
            $couponSetting = $request->request->all();
            $this->getSettingService()->set('coupon', $couponSetting);

            $hiddenMenus = $this->getSettingService()->get('menu_hiddens', array());

            if ($couponSetting['enabled']) {
                unset($hiddenMenus['admin_coupon_generate']);
            } else {
                $hiddenMenus['admin_coupon_generate'] = true;
            }

            $this->getSettingService()->set('menu_hiddens', $hiddenMenus);

            $this->getLogService()->info('coupon', 'setting', '更新优惠码状态', $couponSetting);
            $this->setFlashMessage('success', '优惠码状态已保存！');
        }

        return $this->render('admin/coupon/setting.html.twig', array(
            'couponSetting' => $couponSetting,
        ));
    }

    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    private function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }

    private function getCouponBatchService()
    {
        return $this->createService('Coupon:CouponBatchService');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
