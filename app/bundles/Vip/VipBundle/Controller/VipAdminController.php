<?php
namespace Vip\VipBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class VipAdminController extends BaseController
{

    public function indexAction(Request $request,$type) 
    {
        $fields = $request->query->all();

        $conditions = array(
            'nickname'=>'',
            'level'=>''
        );
        if(!empty($fields)){
            $conditions =$fields;
        }

        if(!empty($fields['nickname'])) {

            $user = $this->getUserService()->getUserByNickname($fields['nickname']);
            $conditions['userId'] = empty($user) ? -1 : $user['id'] ; 
        }

        if($type == "will_expire"){
            $conditions['deadlineLessThan'] = time();
            $order = array('deadline', 'ASC');
        }else if($type == "just_expire"){
            $conditions['deadlineMoreThan'] = time();
            $order = array('deadline', 'DESC');
        }else{
            $order = array('createdTime', 'DESC');
        }

        $paginator = new Paginator(
            $this->get('request'),
            $memberCount = $this->getVipService()->searchMembersCount($conditions),
            20
        );

        $members = $this->getVipService()->searchMembers(
            $conditions,
            $order,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));

        $levels = $this->makeMemberLevelOptions();
        $levels_enabled = $this->makeMemberLevelOptions('enabled');

        return $this->render('VipBundle:VipAdmin:index.html.twig', array(
            'members' => $members ,
            'paginator' => $paginator,
            'memberCount' => $memberCount,
            'levels' => $levels,
            'levels_enabled' => $levels_enabled,
            'users' => $users,
            'type' =>$type
        ));
    }
    
    public function createAction(Request $request)
    {
       
        if($request->getMethod() == 'POST'){
            
            $formData = $request->request->all();

            $user = $this->getUserService()->getUserByNickname($formData['nickname']);

            $member = $this->getVipService()->becomeMember(
                $user['id'], 
                $formData['levelId'], 
                $formData['boughtDuration'], 
                $formData['boughtUnit'], 
                $orderId = 0
            );

            $level = $this->getLevelService()->getLevel($member['levelId']);
             return $this->render('VipBundle:VipAdmin:member-table-tr.html.twig',array(
                'member' => $member,
                'user' => $user,
                'type' => 'all',
                'level' => $level['name']
            ));
        }
        $levels_enabled = $this->makeMemberLevelOptions($operate_type='enabled');
        return $this->render('VipBundle:VipAdmin:modal.html.twig',array(
            'levels_enabled' => $levels_enabled
        ));
    }

    public function nicknameCheckAction( Request $request )
    {
        $nickname = $request->query->get('value');
        list($result, $message) = $this->getVipService()->checkMemberName($nickname);

        if ($result == 'success') {
            $response = array('success' => true, 'message' => '该昵称可以使用');
        } else {
            $response = array('success' => false, 'message' => $message);
        }
        return $this->createJsonResponse($response);
    }


    public function boughtHistoryAction(Request $request)
    {
        $fields = $request->query->all();

        if(!empty($fields)){
            $conditions =$fields;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $memberHistoriesCount = $this->getVipService()->searchMembersHistoriesCount($conditions),
            20
        );

        $memberHistories = $this->getVipService()->searchMembersHistories(
            $conditions,
            array('boughtTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $user = $this->getUserService()->getUser($memberHistories[0]['userId']);
        $levels = $this->makeMemberLevelOptions();

        return $this->render('VipBundle:VipAdmin:bought-history.html.twig',array(
            'memberHistories' => $memberHistories,
            'paginator' => $paginator,
            'userNickname' => $user['nickname'],
            'show_usernick' => -1,
            'levels' => $levels
            ));
    }

    public function boughtListAction(Request $request)
    {
        $fields = $request->query->all();
        $fields['id'] = "";

        if(!empty($fields)){
            $conditions =$fields;
        }
        $paginator = new Paginator(
            $this->get('request'),
            $memberHistorysCount = $this->getVipService()->searchMembersHistoriesCount($conditions),
            20
        );

        $memberHistories = $this->getVipService()->searchMembersHistories(
            $conditions,
            array('boughtTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($memberHistories, 'userId'));

        $levels = $this->makeMemberLevelOptions();

        return $this->render('VipBundle:VipAdmin:bought-list.html.twig',array(
            'memberHistories' => $memberHistories,
            'paginator' => $paginator,
            'menu' => 'member_history',
            'show_usernick' => 1,
            'levels' => $levels,
            'users' => $users
            ));
    }

    public function editAction(Request $request,$userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $member = $this->getVipService()->getMemberByuserId($userId);

        $levels_enabled = $this->makeMemberLevelOptions('enabled');

        if ($request->getMethod() == 'POST') {

            $formData = $request->request->all();
            $member = $this->getVipService()->updateMemberInfo($userId, $formData);
            $level = $this->getLevelService()->getLevel($member['levelId']);
            return $this->render('VipBundle:VipAdmin:member-table-tr.html.twig',array(
                'member' => $member,
                'user' => $user,
                'level' => $level['name'],
                'type' => 'all'
            ));
        }

        return $this->render('VipBundle:VipAdmin:modal.html.twig', array(
            'member' => $member,
            'user' => $user,
            'levels_enabled' => $levels_enabled
        ));
    }
    
    public function cancelAction(Request $request,$userId)
    {
        $member = $this->getVipService()->getMemberByuserId($userId);

        if($request->getMethod() == 'POST'){

            $this->getVipService()->cancelMemberByUserId($userId);
            return $this->createJsonResponse(true);
        }
      
        return $this->render('VipBundle:VipAdmin:cancel-modal.html.twig', array(
            'member' => $member
        ));
    }

    public function orderAction(Request $request)
    {
        return $this->forward('TopxiaAdminBundle:Order:manage', array(
            'request' => $request,
            'type' => 'vip',
            'layout' => 'VipBundle:VipAdmin:layout.html.twig',
        ));
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }    

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function makeMemberLevelOptions($operate_type=array())
    {
        $conditions = $operate_type == 'enabled' ? array('enabled'=>1) : array();
        $levels = $this->getLevelService()->searchLevels(
            $conditions,
            0,
            $this->getLevelService()->searchLevelsCount(array())
        );

        $options = array();
        foreach ($levels as $level) {
            $options[$level['id']] = $level['name'];
        }

        return $options;
    }


}


