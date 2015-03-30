<?php
namespace Custom\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\AdminBundle\Controller\BaseController;

class GroupController extends BaseController
{

	public function IndexAction(Request $request)
    {
		$fields = $request->query->all();

        $conditions = array(
            'status'=>'',
            'title'=>'',
            'ownerName'=>'',
        );

        if(!empty($fields)){
            $conditions =$fields;
        } 

		$paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->searchGroupsCount($conditions),
            10
        );

		$groupinfo=$this->getGroupService()->searchGroups(
                $conditions,
                array('createdTime','desc'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );

        $ownerIds =  ArrayToolkit::column($groupinfo, 'ownerId');
        $owners = $this->getUserService()->findUsersByIds($ownerIds);

		return $this->render('CustomAdminBundle:Group:index.html.twig',array(
			'groupinfo'=>$groupinfo,
            'owners'=>$owners,
			'paginator' => $paginator));
	}

    public function recommendAction(Request $request, $id)
    {
        $group = $this->getGroupService()->getGroup($id);

        $ref = $request->query->get('ref');

        if ($request->getMethod() == 'POST') {

            $number = $request->request->get('number');

            $course = $this->getGroupService()->recommendGroup($id, $number);

            //$user = $this->getUserService()->getUser($course['userId']);

           // return $this->renderCourseTr($id);
        }


        return $this->render('CustomAdminBundle:Group:group-recommend-modal.html.twig', array(
            'group' => $group,
            'ref' => $ref
        ));
    }

    private function renderCourseTr($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $default = $this->getSettingService()->get('default', array());
        return $this->render('TopxiaAdminBundle:Course:tr.html.twig', array(
            'user' => $this->getUserService()->getUser($course['userId']),
            'category' => $this->getCategoryService()->getCategory($course['categoryId']),
            'course' => $course ,
            'default'=>$default
        ));
    }

	  protected function getGroupService()
    {
        return $this->getServiceKernel()->createService('Custom:Group.GroupService');
    }


}