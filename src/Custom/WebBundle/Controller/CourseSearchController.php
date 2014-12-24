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
		  if(isset($options['categoryCode']))
		  {
		  	$cateCode = $options['categoryCode'];
		  	$category = $this->getCategoryService()->getCategoryByCode($cateCode);
           			if(!empty($category)){
           				$options['categoryId'] = $category['id'];
           			} 
		  	unset($options['categoryCode']);
		  }
		  $categoryId = $options['categoryId'];
		  $complexity = $options['complexity'];
		  $price = $options['price'];
		  $firstLevel = $options['firstLevel'];
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
		$total=$this->getCustomCourseSearcheService()->searchCourseCount($options);
		$paginator = new Paginator(
			$this->get('request')
			,$total
			, 18
		);
		$courses = $this->getCustomCourseSearcheService()->searchCourses(
			$options, null,
			$paginator->getOffsetCount(),
			$paginator->getPerPageCount()
		);
		// var_dump($courses);
		return $this->render('TopxiaWebBundle:Course:explore.html.twig', array(
			'categoryId'=>$categoryId,
			'complexity'=>$complexity,
			'price'=>$price,
			'courses'=>$courses,
			'paginator'=>$paginator,
			'total'=>$total,
			'firstLevel' => $firstLevel
		));

	}
	private function getCustomCourseSearcheService(){
		return $this->getServiceKernel()->createService('Custom:Course.CourseSearchService');
	}
	protected function getCategoryService()
	 {
	        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
	 }

}