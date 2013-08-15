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
        $conditions = $this->_prepareConditions($conditions);
        
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

    public  function detailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);
        $user = $this->getUserService()->getuser($order['userId']);
        $course = $this->getCourseService()->getCourse($order['courseId']);
        return $this->render('TopxiaAdminBundle:CourseOrder:detail-modal.html.twig', array(
            'order'=>$order,
            'user'=>$user,
            'course'=>$course
        ));
    }

    private function _prepareConditions($conditions)
    {
        if(empty($conditions['keyword'])){
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if((isset($conditions['keywordType'])) && $conditions['keywordType'] == 'nickname'){
            $user = $this->getUserService()->getUserByNickname($conditions['keyword']);
            if(empty($user)){
                throw new \RuntimeException('该用户昵称不存在！请重新输入...');
            }
            $conditions['keywordType'] = 'userId';
            $conditions['keyword'] = $user['id'];
        }
        return $conditions;
    }

    private function createOrderSearchForm()
    {
        return $this->createFormBuilder()
            ->add('status', 'choice', array(
                'choices' => array('paid' => '已付款', 'created' => '未付款')
            ))
            ->add('payment', 'choice', array(
                'choices' => array('alipay' => '支付宝', 'tenpay'=>'财付通'),
                'empty_value' => '支付方式',
                'required' => false
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
            ->add('keywordType', 'choice', array(
                'choices' => array('sn' => '订单号','nickname' => '用户名','bank' => '银行编号')
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