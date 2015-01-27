<?php
namespace Custom\AdminBundle\Controller;

use Topxia\AdminBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseCarouselController extends BaseController
{
    public function indexAction(Request $request)
    {
        $courseCarousels=$this->getCourseCarouselService()->findAllCourseCarousels();
        $categories=$this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseCarousels,'categoryId'));
        $columns=$this->getColumnService()->findColumnsByIds(ArrayToolkit::column($courseCarousels,'columnId'));
        $columns=ArrayToolkit::index($columns,'id');

        return $this->render('CustomAdminBundle:CourseCarousel:index.html.twig', array(
            'courseCarousels'=>$courseCarousels,
            'categories'=>$categories,
            'columns'=>$columns
        ));
    }

    public function editAction(Request $request,$code)
    {
        if ('POST' == $request->getMethod()){
            $fields=$request->request->all();
            $fields['display']=isset($fields['display'])?1:0;
            $this->getCourseCarouselService()->edit($code,$fields);
        }
        $courseCarousel=$this->getCourseCarouselService()->getCourseCarouselByCode($code);
        $category=$this->getCategoryService()->getCategory($courseCarousel['categoryId']);
        $column=$this->getColumnService()->getColumn($courseCarousel['columnId']);
        return $this->render('CustomAdminBundle:CourseCarousel:edit-modal.html.twig',array(
            'courseCarousel'=>$courseCarousel,
            'category'=>$category,
            'column'=>$column
        ));
    }

    protected function getCourseCarouselService()
    {
        return $this->getServiceKernel()->createService('Custom:CourseCarousel.CourseCarouselService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.CategoryService');
    }

    private function getColumnService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.ColumnService');
    }
}