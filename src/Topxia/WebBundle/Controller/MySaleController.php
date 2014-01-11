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

       
        return $this->render('TopxiaWebBundle:MySale:overview.html.twig', array(
          
        ));
	}


    public function courseListAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $sort  = 'recommended';

        $conditions = array(
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
 
       
        return $this->render('TopxiaWebBundle:MySale:course-list.html.twig', array(
            'courses'=>$courses,
            'paginator' => $paginator
        ));
       
       
    }

     public function courseLinkAction(Request $request,$id)
    {
        $user = $this->getCurrentUser();


        $course = $this->getCourseService()->getCourse($id);


        $mysale=$this->getMySaleService()->getMySaleByProdAndUser('course',$course['id'],$user['id']);


        if(empty($mysale)){

            $mysale=array();

            $mysale['mTookeen'] = $this->getMySaleService()->generateMySaleTookeen();

            if($course['commissionType']=='固定'){

                $mysale['commission']= $course['commission'];

            }else if($course['commissionType']=='立减'){

                $mysale['commission'] = ($course['price']*$course['commission'])/10;

            }

            $mysale['prodType']='course';
            $mysale['prodId']=$course['id'];
            $mysale['prodName']=$course['title'];

            $courseUrl = $this->generateUrl('course_show', array('id' => $course['id']),true);


            $mysale['tUrl']=$courseUrl.'?mc'.$course['id'].'='.$mysale['mTookeen'];

            $mysale['validTime']=$course['saleValidTime'];

            $mysale['userId']=$user['id'];
          

            $this->getMySaleService()->createMySale($mysale);

        }


       
        return $this->render('TopxiaWebBundle:MySale:course-mysale-modal.html.twig', array(
            'mysale'=>$mysale,
            'user'=>$user            
        ));
       
       
    } 
     
    protected function getMySaleService()
    {
        return $this->getServiceKernel()->createService('Sale.MySaleService');
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