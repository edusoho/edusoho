<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ActivityController extends BaseController
{

	public function indexAction(Request $request){

		$conditions = $request->query->all();

        $count = $this->getActivityService()->searchActivityCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $courses = $this->getActivityService()->searchActivitys($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render('TopxiaAdminBundle:Activity:index.html.twig', array(
            'conditions' => $conditions,
            'courses' => $courses ,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator
        ));

	}

	public function deleteAction(Request $request, $id)
    {
        $result = $this->getActivityService()->deleteActivity($id);
        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $id)
    {
        $this->getActivityService()->publishActivity($id);
        return $this->renderActivityTr($id);
    }

    public function closeAction(Request $request, $id)
    {
        $course = $this->getActivityService()->closeActivity($id);
        return $this->renderActivityTr($id);
    }

    private function renderActivityTr($courseId)
    {
        $course = $this->getActivityService()->getActivity($courseId);

        return $this->render('TopxiaAdminBundle:Activity:tr.html.twig', array(
            'user' => $this->getUserService()->getUser($course['userId']),
            'course' => $course ,
        ));
    }

    public function categoryAction(Request $request)
    {
        return $this->forward('TopxiaAdminBundle:Category:embed', array(
            'group' => 'course',
            'layout' => 'TopxiaAdminBundle:Course:layout.html.twig',
        ));
    }




	private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getActivityService(){
        return $this->getServiceKernel()->createService('Activity.ActivityService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

}