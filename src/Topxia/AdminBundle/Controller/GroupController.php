<?php
namespace Topxia\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
class GroupController extends BaseController
{

	public function IndexAction(Request $request){
		$fields = $request->query->all();
        $conditions = array(
            'status'=>'',
            'title'=>'',
            'nickname'=>''
        );

        if(!empty($fields)){
            $conditions =$fields;
        }
		$paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->getAllgroupCount($conditions),
            10
        );
		$groupinfo=$this->getGroupService()->getAllgroupinfo(
			  $conditions,
            'createdTime DESC',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
            );
		return $this->render('TopxiaAdminBundle:Group:index.html.twig',array(
			'groupinfo'=>$groupinfo,
			'paginator' => $paginator));
	}
    public function open_groupAction($id){
        $this->getGroupService()->openGroup($id);
        $groupinfo=$this->getGroupService()->getAllgroupinfo(array('id'=>$id),'createdTime DESC',0,1);
        return $this->render('TopxiaAdminBundle:Group:table-tr.html.twig', array(
            'group' => $groupinfo[0],
        ));
    }
    public function  close_groupAction($id){


        $this->getGroupService()->closeGroup($id);
        $groupinfo=$this->getGroupService()->getAllgroupinfo(array('id'=>$id),'createdTime DESC',0,1);
        return $this->render('TopxiaAdminBundle:Group:table-tr.html.twig', array(
            'group' => $groupinfo[0],
        ));
    }
	  protected function getGroupService()
    {
        return $this->getServiceKernel()->createService('MyGroup.MyGroupService');
    }


}