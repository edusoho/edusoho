<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\WebBundle\Form\UserProfileType;
use Topxia\WebBundle\Form\TeacherProfileType;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Common\FileToolkit;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;


class MySaleController extends BaseController
{

	public function overviewAction(Request $request)
	{
		$user = $this->getCurrentUser();

        $yesCommission = $this->getCommissionService()->computeMyCommissionsOfYesterday($user['id']);

        $monthCommission = $this->getCommissionService()->computeMyCommissionsOfMonth($user['id']);

        $lastCommission = $this->getCommissionService()->computeMyCommissionsOfLast($user['id']);

        $commission = $this->getCommissionService()->computeMyCommissions($user['id']);

       
        return $this->render('TopxiaWebBundle:Sale:overview.html.twig', array(
            'yesCommission'=>$yesCommission['commissions'],
            'monthCommission'=>$monthCommission['commissions'],
            'lastCommission'=> $lastCommission['commissions'],
            'commission'=>$commission['commissions']
          
        ));
	}

    public function commissionListAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $sort  = 'latest';

        $conditions = $request->query->all();

        $conditions['salerId'] = $user['id'];

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCommissionService()->searchCommissionCount($conditions)
            ,12
        );


        $commissions = $this->getCommissionService()->searchCommissions(
            $conditions, $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds=ArrayToolkit::column($commissions,'orderId');

        $orders = $this->getOrderService()->findOrdersByIds($orderIds);

        $linksaleIds=ArrayToolkit::column($commissions,'saleId');

        $linksales = $this->getLinkSaleService()->findLinkSalesByIds($linksaleIds);

        $offsaleIds=ArrayToolkit::column($commissions,'saleId');

        $offsales = $this->getOffSaleService()->findOffSalesByIds($offsaleIds);

        $buyerIds=ArrayToolkit::column($commissions,'buyerId');

        $buyers = $this->getUserService()->findUsersByIds($buyerIds);
 
       
        return $this->render('TopxiaWebBundle:Sale:commission-list.html.twig', array(
            'commissions'=>$commissions,
            'orders' => $orders,
            'linksales' => $linksales,
            'offsales' => $offsales,
            'buyers' => $buyers,
            'paginator' => $paginator
        ));       
    }


    public function linkCourseListAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $sort  = 'recommended';

        $conditions = array(
            'price'=>1,
            'status' => 'published',
            'recommended' => ($sort == 'recommended') ? null : null
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            ,12
        );


        $courses = $this->getCourseService()->searchCourses(
            $conditions, $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
 
       
        return $this->render('TopxiaWebBundle:Sale:link-course-list.html.twig', array(
            'courses'=>$courses,
            'paginator' => $paginator
        ));
       
       
    }

    public function linkCourseLinkAction(Request $request,$id)
    {
        $user = $this->getCurrentUser();


        $course = $this->getCourseService()->getCourse($id);


        $linksale=$this->getLinkSaleService()->getLinkSaleByProdAndUser('course',$course['id'],$user['id']);


        if(empty($linksale)){

            $linksale=array();

            $linksale['mTookeen'] = $this->getLinkSaleService()->generateLinkSaleTookeen();
           

            $linksale['adCommissionType']= empty($course['adCommissionType']) ?'ratio':$course['adCommissionType'];

            $linksale['adCommission']= empty($course['adCommission'])?'30':$course['adCommission'];

            $linksale['adCommissionDay']= empty($course['adCommissionDay'])?'30':$course['adCommissionDay'];
            $linksale['customized']=0;
           
            $linksale['saleType']='linksale-course';
            $linksale['prodType']='course';
            $linksale['prodId']=$course['id'];
            $linksale['prodName']=$course['title'];

            $courseUrl = $this->generateUrl('course_show', array('id' => $course['id']),true);


            $linksale['tUrl']=$courseUrl.'?mc'.$course['id'].'='.$linksale['mTookeen'];

            $linksale['reduceType'] = 'quota';
            $linksale['reducePrice'] = 0.00;
            $linksale['validTime']=0;//优惠有效期



            $linksale['partnerId']=$user['id'];

            $linksale['managerId']= 2;
          

            $this->getLinkSaleService()->createLinkSale($linksale);

        }


       
        return $this->render('TopxiaWebBundle:Sale:link-course-modal.html.twig', array(
            'linksale'=>$linksale,
            'user'=>$user            
        ));
       
       
    }


    public function linkCourseReduceAction(Request $request,$id)
    {
       
       
       
    }

    public function linkWebLinkAction(Request $request)
    {
        $user = $this->getCurrentUser();


        $prodType='course';
        $prodId=0;
        $prodName='所有课程';


        $linksale=$this->getLinkSaleService()->getLinkSaleByProdAndUser($prodType,$prodId,$user['id']);


        if(empty($linksale)){

            $linksale=array();

            $linksale['mTookeen'] = $this->getLinkSaleService()->generateLinkSaleTookeen();
           

            $linksale['adCommissionType']= 'ratio';

            $lswSetting = $this->getSettingService()->get('linksaleWebSetting', array());

            //默认推广链接30天内有效
            if(empty($lswSetting['webCommission'])){
                 $lswSetting['webCommissionType'] = 'ratio';
                 $lswSetting['webCommission'] = 5;//网站推广，获取所有注册用户的5%的佣金
                 $lswSetting['webCommissionDay'] = 30;
            }

            $linksale['adCommissionType']= $lswSetting['webCommissionType'];
            $linksale['adCommission']= $lswSetting['webCommission'];  
            $linksale['adCommissionDay']= empty($lswSetting['webCommissionDay'])?'30':$lswSetting['webCommissionDay'];
            $linksale['customized']=0;
           
            $linksale['saleType']='linksale-web';
            $linksale['prodType']=$prodType;
            $linksale['prodId']=$prodId;
            $linksale['prodName']=$prodName;

            $webUrl = $this->generateUrl('homepage',array(),true);

            $linksale['tUrl']=$webUrl.'?mu='.$linksale['mTookeen'];

            $linksale['reduceType'] = 'quota';
            $linksale['reducePrice'] = 0.00;         


            $linksale['validTime']=0;//优惠有效期

            $linksale['partnerId']=$user['id'];

            $linksale['managerId']= 2;
          
            $linksale = $this->getLinkSaleService()->createLinkSale($linksale);

        }
       
        return $this->render('TopxiaWebBundle:Sale:link-web.html.twig', array(
            'linksale'=>$linksale,
            'user'=>$user            
        ));
       
       
    }

    

     public function offsaleCourseListAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $sort  = 'recommended';

        $conditions = array(
            'price'=>1,
            'status' => 'published',
            'recommended' => ($sort == 'recommended') ? null : null
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions)
            ,12
        );


        $courses = $this->getCourseService()->searchCourses(
            $conditions, $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
 
       
        return $this->render('TopxiaWebBundle:Sale:offsale-course-list.html.twig', array(
            'courses'=>$courses,
            'paginator' => $paginator
        ));
       
       
    }


    public function offsaleCourseCodeAction(Request $request,$id)
    {
        $user = $this->getCurrentUser();


        $course = $this->getCourseService()->getCourse($id);


        $offsale=$this->getOffSaleService()->getOffSaleBySPPP('invite-course',$user['id'],'course',$course['id']);


        if(empty($offsale)){

            $offsale=array();

            $offsale['saleType'] = 'invite-course';
            $offsale['prodType'] = 'course';
            $offsale['prodName'] = $course['title'];
            $offsale['prodId']  = $course['id'];
            $offsale['promoName'] = $course['title'].'推广码';
            $offsale['promoCode']= $this->getOffSaleService()->generateOffSaleCode('');

            
            $offsale['adCommissionType']= empty($course['adCommissionType']) ?'ratio':$course['adCommissionType'];

            $offsale['adCommission']= empty($course['adCommission'])?'30':$course['adCommission'];

            $offsale['adCommissionDay']= empty($course['adCommissionDay'])?'30':$course['adCommissionDay'];
            $offsale['customized']=0;


            $offsale['reduceType'] = 'quota';
            $offsale['reducePrice'] = 0;
            $offsale['validTime']==0;//优惠有效期

            $offsale['reuse']= '可以';
            $offsale['valid']= '有效';
           
            $offsale['partnerId']= $user['id'];
            $offsale['managerId']= 2;
         

           
          
          

            $this->getOffSaleService()->createOffSale($offsale);

        }


       
        return $this->render('TopxiaWebBundle:Sale:off-course-modal.html.twig', array(
            'offsale'=>$offsale,
            'user'=>$user            
        ));
       
       
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

    protected function getCommissionService()
    {
        return $this->getServiceKernel()->createService('Sale.CommissionService');
    }
     
    protected function getLinkSaleService()
    {
        return $this->getServiceKernel()->createService('Sale.LinkSaleService');
    }

    protected function getOffSaleService()
    {
        return $this->getServiceKernel()->createService('Sale.OffSaleService');
    }
    

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }
 
    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}