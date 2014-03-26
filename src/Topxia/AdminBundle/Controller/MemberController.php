<?php
namespace Topxia\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MemberController extends BaseController
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
            $conditions['deadlineMoreThan'] = time();
        }else if($type == "just_expire"){
            $conditions['deadlineLessThan'] = time();
        }

        $paginator = new Paginator(
            $this->get('request'),
            $memberCount = $this->getMemberService()->searchMembersCount($conditions),
            20
        );

        $members = $this->getMemberService()->searchMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));

        $levels = $this->makeMemberLevelOptions();

        return $this->render('TopxiaAdminBundle:Member:index.html.twig', array(
            'members' => $members ,
            'paginator' => $paginator,
            'memberCount' => $memberCount,
            'levels' => $levels,
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
             return $this->render('TopxiaAdminBundle:Member:member-table-tr.html.twig',array(
                'member' => $member,
                'user' => $user,
                'type' => 'all',
                'level' => $level['name']
            ));
        }
        $levels = $this->makeMemberLevelOptions();
        return $this->render('TopxiaAdminBundle:Member:modal.html.twig',array(
            'levels' => $levels
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

        return $this->render('TopxiaAdminBundle:Member:bought-history.html.twig',array(
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

        return $this->render('TopxiaAdminBundle:Member:bought-list.html.twig',array(
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

         $levels = $this->makeMemberLevelOptions();

        if ($request->getMethod() == 'POST') {

            $formData = $request->request->all();
            $member = $this->getMemberService()->updateMemberInfo($userId, $formData);
            $level = $this->getLevelService()->getLevel($member['levelId']);
            return $this->render('TopxiaAdminBundle:Member:member-table-tr.html.twig',array(
                'member' => $member,
                'user' => $user,
                'level' => $level['name'],
                'type' => 'all'
            ));
        }

        return $this->render('TopxiaAdminBundle:Member:modal.html.twig', array(
            'member' => $member,
            'user' => $user,
            'levels' => $levels
        ));
    }
    
    public function cancelAction(Request $request,$userId)
    {
        $member = $this->getMemberService()->getMemberByuserId($userId);

        if($request->getMethod() == 'POST'){

            $this->getMemberService()->cancelMemberByUserId($userId);
            return $this->createJsonResponse(true);
        }
      
        return $this->render('TopxiaAdminBundle:Member:cancel-modal.html.twig', array(
            'member' => $member
        ));
    }

    protected function getMemberService()
    {
        return $this->getServiceKernel()->createService('User.MemberService');
    }    

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('User.LevelService');
    }

    protected function makeMemberLevelOptions()
    {
        $levels = $this->getLevelService()->searchLevels(
            $conditions=array(),
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


