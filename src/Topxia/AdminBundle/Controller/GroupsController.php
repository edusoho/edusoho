<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Imagine\Gd\Imagine;
use Topxia\WebBundle\DataDict\ContentStatusDict;
use Topxia\WebBundle\DataDict\ContentTypeDict;
use Topxia\Service\Content\Type\ContentTypeFactory;

class GroupsController extends BaseController
{
    public function groupIndexAction(Request $request)
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

        return $this->render('TopxiaAdminBundle:Operation:group.index.html.twig',array(
            'groupinfo'=>$groupinfo,
            'owners'=>$owners,
            'paginator' => $paginator));
    }

    public function  closeGroupAction($id)
    {
        $this->getGroupService()->closeGroup($id);

        $groupinfo=$this->getGroupService()->getGroup($id);
        
        $owners=$this->getUserService()->findUsersByIds(array('0'=>$groupinfo['ownerId']));

        return $this->render('TopxiaAdminBundle:Operation:group-tr.html.twig', array(
            'group' => $groupinfo,
            'owners'=>$owners,
        ));
    }

    public function openGroupAction($id)
    {
        $this->getGroupService()->openGroup($id);

        $groupinfo=$this->getGroupService()->getGroup($id);

        $owners=$this->getUserService()->findUsersByIds(array('0'=>$groupinfo['ownerId']));

        return $this->render('TopxiaAdminBundle:Operation:group-tr.html.twig', array(
            'group' => $groupinfo,
            'owners'=>$owners,
        ));
    }

    public function transferGroupAction(Request $request,$groupId)
    {
        $data=$request->request->all();

        $user=$this->getUserService()->getUserByNickname($data['user']['nickname']);

        $group=$this->getGroupService()->getGroup($groupId);

        $ownerId=$group['ownerId'];

        $member=$this->getGroupService()->getMemberByGroupIdAndUserId($groupId,$ownerId);

        $this->getGroupService()->updateMember($member['id'],array('role'=>'member'));

        $this->getGroupService()->updateGroup($groupId,array('ownerId'=>$user['id']));

        $member=$this->getGroupService()->getMemberByGroupIdAndUserId($groupId,$user['id']);

        if($member){
            $this->getGroupService()->updateMember($member['id'],array('role'=>'owner'));
        }else{
            $this->getGroupService()->addOwner($groupId,$user['id']);
        }

        return new Response("success");
    }

    protected function getGroupService()
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

     protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }

    protected function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    private function filterSort($sort)
    {
        switch ($sort) {
            case 'byPostNum':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('postNum','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byStick':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byCreatedTime':
                $orderBys=array(
                    array('createdTime','DESC'),
                );
                break;
            case 'byLastPostTime':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('lastPostTime','DESC'),
                );
                break;
            case 'byPostNum':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('postNum','DESC'),
                );
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
        }
        return $orderBys;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}