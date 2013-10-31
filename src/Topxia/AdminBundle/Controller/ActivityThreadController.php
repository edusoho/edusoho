<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ActivityThreadController extends BaseController
{

    public function indexAction (Request $request)
    {

		$conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
        $activitys = $this->getActivityService()->findActivitysByIds(ArrayToolkit::column($threads, 'activityId'));
       

    	return $this->render('TopxiaAdminBundle:ActivityThread:index.html.twig', array(
    		'paginator' => $paginator,
            'threads' => $threads,
            'users'=> $users,
            'activitys' => $activitys,
		));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getThreadService()->deleteThread($id);
        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids ? : array() as $id) {
            $this->getThreadService()->deleteThread($id);
        }
        return $this->createJsonResponse(true);
    }
    

     private function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity.ActivityService');
    }

   private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Activity.ThreadService');
    }

    private function getMaterialService(){
        return $this->getServiceKernel()->createService('Activity.MaterialService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    private function getLocationService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.LocationService');
    }

    private function getPhotoService(){
        return $this->getServiceKernel()->createService('Photo.PhotoService');   
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getCourseService(){
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

}