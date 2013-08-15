<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseOrderController extends BaseController
{

    public function manageAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $searchForm = $this->createOrderSearchForm();
        $searchForm->bind($request);
        $conditions = $searchForm->getData();
        
        $paginator = new Paginator(
            $this->get('request'),
            $this->getOrderService()->searchOrderCount($conditions),
            20
        );

        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:CourseOrder:index.html.twig', array(
            'searchForm' => $searchForm->createView(),
            'orders' => $orders ,
            'paginator' => $paginator
        ));

    }

    private function createOrderSearchForm()
    {
        return $this->createFormBuilder()
            ->add('status', 'choice', array(
                'choices' => array('paid' => '已付款', 'created' => '未付款')
            ))
            ->add('payment', 'choice', array(
                'choices' => array('alipay' => '支付宝', 'tenpay'=>'财付通', 'none' => '无')
            ))
            ->add('isGift', 'choice', array(
                'choices' => array(0 => '非礼品课程', 1 => '礼品课程')
            ))

            ->add('paidStartTime', 'date', array(
                'widget' => 'single_text',
                'input' => 'timestamp',
                'required' => false
            ))
            ->add('paidEndTime', 'date',  array(
                'widget' => 'single_text',
                'input' => 'timestamp',
                'required' => false
            ))
            ->add('createStartTime', 'date', array(
                'widget' => 'single_text',
                'input' => 'timestamp',
                'required' => false
            ))
            ->add('createEndTime', 'date',  array(
                'widget' => 'single_text',
                'input' => 'timestamp',
                'required' => false
            ))

            ->add('keywordType', 'choice', array(
                'choices' => array(
                    'sn' => '订单号',
                    'nickname' => '用户名',
                    'courseTitle' => '课程名',
                    'bank' => '银行编号'
                ),
            ))
            ->add('keyword', 'text', array('required' => false))
            ->getForm();
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}