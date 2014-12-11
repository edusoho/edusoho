<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
class CourseSearchController extends BaseController
{	
	//拉取所有的课程，直接到选课页面
	public function indexAction(Request $request)
	{
		$options = $request->query->all();
		  $categoryId = $options['categoryId'];
		  $complexity = $options['complexity'];
		   $price = $options['price'];
		if($options['complexity']=='all'){
		   unset($options['complexity']);
		}
		if($options['categoryId']=='all'){
		   unset($options['categoryId']);
		}
		if($options['price']=='all'){
		   unset($options['price']);
		}	
		if(!empty($options['price'])){
		$price = $options['price'];
		     if(strpbrk ( $price , "_" )){
		     	$prices = explode("_", $price);
		     	$options['minPrice']=$prices[0];
		     	$options['maxPrice']=$prices[1];
		     }
		     elseif($price=='10--'){
		     	$options['maxPrice']=10;
		     }
		     elseif($price=='100++'){
		     	$options['minPrice']=100;
		     }
		     unset($options['price']);
		   
		}
	
		$paginator = new Paginator(
			$this->get('request'),
			$this->getCustomCourseSearcheService()->searchCourseCount($options)
			, 10
		);
		$courses = $this->getCustomCourseSearcheService()->searchCourses(
			$options, null,
			$paginator->getOffsetCount(),
			$paginator->getPerPageCount()
		);
		return $this->render('TopxiaWebBundle:Course:explore.html.twig', array(
			'categoryId'=>$categoryId,
			'complexity'=>$complexity,
			'price'=>$price,
			'courses'=>$courses,
			'paginator'=>$paginator
		));

	}
	private function getCustomCourseSearcheService(){
		return $this->getServiceKernel()->createService('Custom:Course.CourseSearchService');
	}

}