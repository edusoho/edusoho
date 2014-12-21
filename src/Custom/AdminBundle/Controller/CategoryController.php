<?php
namespace Custom\AdminBundle\Controller;
use Topxia\AdminBundle\Controller\BaseController as BaseController; 

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Topxia\Service\Common\ServiceException;


class CategoryController extends BaseController
{

    public function isSearchAction(Request $Request,$id){
        $category = $this->getCategoryService()->getCategory($id);
        if(empty($category)){
            throw $this->createNotFoundException();
        }
        $isSearch = $category['isSearch'];
        if($isSearch == 'active'){
            $isSearch = 'none';
        }else{
            $isSearch = 'active';
        }
        $category['isSearch']=$isSearch;
        
        $this->getCategoryService()->updateCategoryIsSearch($id,$category);
        // return $this->createJsonResponse(true);
           $group = $this->getCategoryService()->getGroup($category['groupId']);
        $categories = $this->getCategoryService()->getCategoryTree($category['groupId']);
        return $this->render('TopxiaAdminBundle:Category:tbody.html.twig', array(
            'categories' => $categories,
            'group' => $group
        ));
    }



    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.CategoryService');
    }




	private function getTagService()
	{
        		return $this->getServiceKernel()->createService('Taxonomy.TagService');
	}
	private function getCustomTagService()
	{
       	 	return $this->getServiceKernel()->createService('Custom:Taxonomy.TagService');
	}

}