<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class PhotoController extends BaseController
{


	public function indexAction(Request $request){

		$conditions = $request->query->all();

        $count = $this->getPhotoService()->searchPhotoCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $courses = $this->getPhotoService()->searchPhotos($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        return $this->render('TopxiaAdminBundle:Photo:index.html.twig', array(
            'conditions' => $conditions,
            'courses' => $courses ,
            'paginator' => $paginator
        ));

	}

	public function deleteAction(Request $request, $id)
    {
        $result = $this->getPhotoService()->deletePhoto($id);
        return $this->createJsonResponse(true);
    }



	private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getPhotoService(){
        return $this->getServiceKernel()->createService('Photo.PhotoService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }


}