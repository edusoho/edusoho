<?php
namespace Member\MemberBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class MemberAdminController extends BaseController
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
            $memberCount = $this->getMemberService()->searchMembersCount($conditions),
            20
        );

        $members = $this->getMemberService()->searchMembers(
            $conditions,
            $order,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));

        $levels = $this->makeMemberLevelOptions();
        $levels_enabled = $this->makeMemberLevelOptions('enabled');

        return $this->render('MemberBundle:MemberAdmin:index.html.twig', array(
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
            $member = $this->getMemberService()->createMember($formData);
            $user = $this->getUserService()->getUser($member['userId']);
            $level = $this->getLevelService()->getLevel($member['levelId']);
             return $this->render('MemberBundle:MemberAdmin:member-table-tr.html.twig',array(
                'member' => $member,
                'user' => $user,
                'type' => 'all',
                'level' => $level['name']
            ));
        }
        $levels_enabled = $this->makeMemberLevelOptions($operate_type='enabled');
        return $this->render('MemberBundle:MemberAdmin:modal.html.twig',array(
            'levels_enabled' => $levels_enabled
        ));
    }

    public function nicknameCheckAction( Request $request )
    {
        $nickname = $request->query->get('value');
        list($result, $message) = $this->getMemberService()->checkMemberName($nickname);

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
            $memberHistoriesCount = $this->getMemberService()->searchMembersHistoriesCount($conditions),
            20
        );

        $memberHistories = $this->getMemberService()->searchMembersHistories(
            $conditions,
            array('boughtTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $levels = $this->makeMemberLevelOptions();
        return $this->render('MemberBundle:MemberAdmin:bought-history.html.twig',array(
            'memberHistories' => $memberHistories,
            'paginator' => $paginator,
            'userNickname' => $memberHistories[0]['userNickname'],
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
            $memberHistorysCount = $this->getMemberService()->searchMembersHistoriesCount($conditions),
            20
        );

        $memberHistories = $this->getMemberService()->searchMembersHistories(
            $conditions,
            array('boughtTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $levels = $this->makeMemberLevelOptions();

        return $this->render('MemberBundle:MemberAdmin:bought-list.html.twig',array(
            'memberHistories' => $memberHistories,
            'paginator' => $paginator,
            'menu' => 'member_history',
            'show_usernick' => 1,
            'levels' => $levels
            ));
    }

    public function editAction(Request $request,$userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $member = $this->getMemberService()->getMemberByuserId($userId);

        $levels_enabled = $this->makeMemberLevelOptions('enabled');

        if ($request->getMethod() == 'POST') {

            $formData = $request->request->all();
            $member = $this->getMemberService()->updateMemberInfo($userId, $formData);
            $level = $this->getLevelService()->getLevel($member['levelId']);
            return $this->render('MemberBundle:MemberAdmin:member-table-tr.html.twig',array(
                'member' => $member,
                'user' => $user,
                'level' => $level['name'],
                'type' => 'all'
            ));
        }

        return $this->render('MemberBundle:MemberAdmin:modal.html.twig', array(
            'member' => $member,
            'user' => $user,
            'levels_enabled' => $levels_enabled
        ));
    }
    
    public function cancelAction(Request $request,$userId)
    {
        $member = $this->getMemberService()->getMemberByuserId($userId);

        if($request->getMethod() == 'POST'){

            $this->getMemberService()->cancelMemberByUserId($userId);
            return $this->createJsonResponse(true);
        }
      
        return $this->render('MemberBundle:MemberAdmin:cancel-modal.html.twig', array(
            'member' => $member
        ));
    }

    protected function getMemberService()
    {
        return $this->getServiceKernel()->createService('Member:Member.MemberService');
    }    

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Member:Member.LevelService');
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


