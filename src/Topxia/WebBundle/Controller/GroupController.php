<?php

namespace Topxia\WebBundle\Controller;
use Topxia\Common\FileToolkit;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class GroupController extends BaseController 
{

    public function indexAction() 
    {   
        $mycreatedGroup = array();
        $myJoinGroup = array();

        $activeGroup = $this->getGroupService()->searchGroups(array('enum'=>'open',),  array('memberNum', 'DESC'),0, 16);
    
        $latedWeekTime=time()-30*24*60*60;

        $recentlyThread = $this->getThreadService()->searchThreads(array('createdTime'=>$latedWeekTime,'enum'=>'open'),$this->filterSort('byPostNum'),0, 15);

        $ownerIds = array_merge(
            ArrayToolkit::column($recentlyThread, 'userId')
        );
        $groupIds = array_merge(
            ArrayToolkit::column($recentlyThread, 'groupId')
        );
        $owner=$this->getUserService()->findUsersByIds($ownerIds);
        $groups=$this->getGroupService()->getGroupsByids($groupIds);

        $user = $this->getCurrentUser();

        if ($user['id']) {
            $members=$this->getGroupService()->searchMembers(array('userId'=>$user['id']),array('createdTime',"DESC"),0,
            24);

            $groupIds = array_merge(
            ArrayToolkit::column($members, 'groupId')
            );
            $myJoinGroup=$this->getGroupService()->getGroupsByids($groupIds);
            $mycreatedGroup = $this->getGroupService()->searchGroups(array('ownerId'=>$user['id'],'enum'=>'open'),array('createdTime','DESC'), 0, 8);
        }
        return $this->render("TopxiaWebBundle:Group:index.html.twig", array(
                    'activeGroup' => $activeGroup,
                    'mycreatedGroup' => $mycreatedGroup,
                    'myjionGroup' => $myJoinGroup,
                    'owner'=>$owner,
                    'groupinfo'=>$groups,
                    'user'=>$user,  
                    'recentlyThread'=>$recentlyThread,
        ));
    }

    public function addGroupAction(Request $request) 
    {
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
            return $this->createMessageResponse('info', '目前只允许管理员创建小组!');
        }
        $user = $this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $mygroup = $request->request->all();
            $group = array(
                'title' => $mygroup['group']['grouptitle'],
                'about' => $mygroup['group']['about'],
            );
            $group = $this->getGroupService()->addGroup($user,$group);
            return $this->redirect($this->generateUrl('group_index',array('id'=>$group['id'])));
        }

        return $this->render("TopxiaWebBundle:Group:groupadd.html.twig");
    }
    public function memberCenterAction()
    {
        
        $user=$this->getCurrentUser();

        $groupsCount=$this->getGroupService()->searchMembersCount(array('userId'=>$user['id']));
        $members=$this->getGroupService()->searchMembers(array('userId'=>$user['id']),array('createdTime',"DESC"),0,
        9);
        $groupIds = array_merge(
            ArrayToolkit::column($members, 'groupId')
        );
        $groups=$this->getGroupService()->getGroupsByids($groupIds);
        $ownThreads=$this->getThreadService()->searchThreads(array('userId'=>$user['id']),array(array('createdTime','DESC')),0,10);
    
        $groupIds = array_merge(
            ArrayToolkit::column($ownThreads, 'groupId')
        );
        $threadsCount=$this->getThreadService()->searchThreadsCount(array('userId'=>$user['id']));
        $groupsAsOwnThreads=$this->getGroupService()->getGroupsByids($groupIds);

        $userIds = array_merge(
            ArrayToolkit::column($ownThreads, 'lastPostMemberId')
        );
        $lastPostMembers=$this->getUserService()->findUsersByIds($userIds);

        $postThreads=$this->getThreadService()->searchPosts(array('userId'=>$user['id']),array('createdTime','DESC'),0,10);
        $threadIds = array_merge(
            ArrayToolkit::column($postThreads, 'threadId')
        );
        $postsCount=$this->getThreadService()->searchPostsCount(array('userId'=>$user['id']));
        $threads=$this->getThreadService()->getThreadsByIds($threadIds);
        $groupIdsAsPostThreads = array_merge(
            ArrayToolkit::column($threads, 'groupId')
        );
        $groupsAsPostThreads=$this->getGroupService()->getGroupsByids($groupIdsAsPostThreads);
        return $this->render("TopxiaWebBundle:Group:groupMemberCenter.html.twig",array(
            'user'=>$user,
            'groups'=>$groups,
            'postThreads'=>$postThreads,
            'threads'=>$threads,
            'threadsCount'=>$threadsCount,
            'postsCount'=>$postsCount,
            'lastPostMembers'=>$lastPostMembers,
            'groupsAsPostThreads'=>$groupsAsPostThreads,
            'groupsAsOwnThreads'=>$groupsAsOwnThreads,
            'ownThreads'=>$ownThreads,
            'groupsCount'=>$groupsCount));

    }
    public function searchAction(Request $request)
    {

        $key="";
        $key=$request->query->get('key');

        $paginator=new Paginator(
            $this->get('request'),
            $this->getGroupService()->searchGroupsCount(array('title'=>$key,'enum'=>'open')),
            24
            );
        $groups=$this->getGroupService()->searchGroups(array('title'=>$key,'enum'=>'open'),array('createdTime',"DESC"),$paginator->getOffsetCount(),
                $paginator->getPerPageCount());
        return $this->render("TopxiaWebBundle:Group:search.html.twig",array(
            'paginator'=>$paginator,
            'groups'=>$groups,
          ));
    }

    public function memberJoinAction()
    {
        $user=$this->getCurrentUser();


        $createdGroups=$this->getGroupService()->searchGroups(array('ownerId'=>$user['id']),array('createdTime',"DESC"),0,
                1000);
        $paginator=new Paginator(
            $this->get('request'),
            $this->getGroupService()->searchMembersCount(array('userId'=>$user['id'])),
            12
            );

        $members=$this->getGroupService()->searchMembers(array('userId'=>$user['id']),array('createdTime',"DESC"),$paginator->getOffsetCount(),
                $paginator->getPerPageCount());

        $groupIds = array_merge(
            ArrayToolkit::column($members, 'groupId')
        );
        $groups=$this->getGroupService()->getGroupsByids($groupIds);
        return $this->render("TopxiaWebBundle:Group:groupMemberJoin.html.twig",array(
            'user'=>$user,
            'createdGroups'=>$createdGroups,
            'paginator'=>$paginator,
            'groups'=>$groups));

    }

    public function memberThreadsAction()
    {
        $user=$this->getCurrentUser();

        $paginator=new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchThreadsCount(array('userId'=>$user['id'])),
            12
            );

        $threads=$this->getThreadService()->searchThreads(array('userId'=>$user['id']),array(array('createdTime',"DESC")),$paginator->getOffsetCount(),
                $paginator->getPerPageCount());

        $groupIds = array_merge(
            ArrayToolkit::column($threads, 'groupId')
        );

        $userIds = array_merge(
            ArrayToolkit::column($threads, 'lastPostMemberId')
        );
        $lastPostMembers=$this->getUserService()->findUsersByIds($userIds);
        $groups=$this->getGroupService()->getGroupsByids($groupIds);
        return $this->render("TopxiaWebBundle:Group:groupMemberThreads.html.twig",array(
            'user'=>$user,
            'paginator'=>$paginator,
            'lastPostMembers'=>$lastPostMembers,
            'threads'=>$threads,
            'groups'=>$groups));

    }

    public function memberPostsAction()
    {
        $user=$this->getCurrentUser();

        $paginator=new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchPostsCount(array('userId'=>$user['id'])),
            12
            );

        $postThreads=$this->getThreadService()->searchPosts(array('userId'=>$user['id']),array('createdTime',"DESC"),$paginator->getOffsetCount(),
                $paginator->getPerPageCount());

        $threadIds = array_merge(
            ArrayToolkit::column($postThreads, 'threadId')
        );
        $threads=$this->getThreadService()->getThreadsByIds($threadIds);
        $groupIdsAsPostThreads = array_merge(
            ArrayToolkit::column($threads, 'groupId')
        );
        $groupsAsPostThreads=$this->getGroupService()->getGroupsByids($groupIdsAsPostThreads);
        return $this->render("TopxiaWebBundle:Group:groupMemberPosts.html.twig",array(
            'user'=>$user,
            'paginator'=>$paginator,
            'threads'=>$threads,
            'groupsAsPostThreads'=>$groupsAsPostThreads,
            'postThreads'=>$postThreads));

    }

    public function groupIndexAction(Request $request,$id) 
    {

        $group = $this->getGroupService()->getGroup($id);

        if($group['enum']=="close"){
            return $this->createMessageResponse('info','该小组已被关闭');
        }

        $recentlyJoinMember=$this->getGroupService()->searchMembers(array('groupId'=>$id,'role'=>'member'),array('createdTime','DESC'),0,12);

        $memberIds = array_merge(
            ArrayToolkit::column($recentlyJoinMember, 'userId')
        );

        $user=$this->getCurrentUser();

        $userIsGroupMember=$this->getGroupService()->getMemberByGroupIdAndUserId($id,$user['id']);
        $recentlyMembers=$this->getUserService()->findUsersByIds($memberIds);

        $filters = $this->getThreadSearchFilters($request);

        $conditions = $this->convertFiltersToConditions($id, $filters);  
    
        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchThreadsCount($conditions),
            $conditions['num']  
        );
            
        $threads=$this->getThreadService()->searchThreads(
            $conditions,
            $this->filterSort($filters['sort']),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
            );

        $ownerIds = array_merge(
            ArrayToolkit::column($threads, 'userId')
        );

        $userIds = array_merge(
            ArrayToolkit::column($threads, 'lastPostMemberId')
        );

        $owner=$this->getUserService()->findUsersByIds($ownerIds);

        $lastPostMembers=$this->getUserService()->findUsersByIds($userIds);

        $activeMembers=$this->getGroupService()->searchMembers(array('groupId'=>$id,'role'=>'member'),array('postNum','DESC'),0,12);

        $memberIds = array_merge(
            ArrayToolkit::column($activeMembers, 'userId')
        );

        $groupCount=$this->getGroupService()->searchGroupsCount(array('enum'=>'open'));

        $start=$groupCount>12 ? rand(0,$groupCount-12) : 0 ;

        $deserveGroup=$this->getGroupService()->searchGroups(array('enum'=>'open',),array('createdTime','DESC'),$start,12);

        $members=$this->getUserService()->findUsersByIds($memberIds);
        return $this->render("TopxiaWebBundle:Group:groupindex.html.twig", array(
                    'groupinfo' => $group,
                    'is_groupmember' => $this->getGroupMemberRole($id),
                    'recentlyJoinMember'=>$recentlyJoinMember,
                    'owner'=>$owner,
                    'user'=>$user,
                    'id'=>$id,
                    'deserveGroup'=>$deserveGroup,
                    'threads'=>$threads,
                    'paginator'=>$paginator,
                    'owner'=>$owner,
                    'condition'=>$filters,
                    'lastPostMembers'=>$lastPostMembers,
                    'userIsGroupMember'=>$userIsGroupMember,
                    'members'=>$recentlyMembers,
                   
        ));
    }

    public function groupMemberAction(Request $request,$id) 
    {
        $group = $this->getGroupService()->getGroup($id);
        if($group['enum']=="close"){
            return $this->createMessageResponse('info','该小组已被关闭');
        }
        $user=$this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->getMembersCountByGroupId($id),
            30
        );
        $Members=$this->getGroupService()->searchMembers(array('groupId'=>$id,'role'=>'member'),array('createdTime','DESC'),$paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        $memberIds = array_merge(
            ArrayToolkit::column($Members, 'userId')
        );

        $users=$this->getUserService()->findUsersByIds($memberIds);
        $owner=$this->getUserService()->getUser($group['ownerId']);

        $groupAdmin=$this->getGroupService()->searchMembers(array('groupId'=>$id,'role'=>'admin'),array('createdTime','DESC'),$paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        $groupAdminIds = array_merge(
            ArrayToolkit::column($groupAdmin, 'userId')
        );

        $usersLikeAdmin=$this->getUserService()->findUsersByIds($groupAdminIds);
        return $this->render("TopxiaWebBundle:Group:groupmember.html.twig", array(
                    'groupinfo' => $group,
                    'is_groupmember' => $this->getGroupMemberRole($id),
                    'groupmember_info'=>$Members,
                    'owner_info'=>$owner,
                    'paginator'=>$paginator,
                    'members'=>$users,
                    'usersLikeAdmin'=>$usersLikeAdmin,
                    'groupAdmin'=>$groupAdmin,
        ));
    }

    public function deleteMembersAction(Request $request,$id)
    {
        $user=$this->getCurrentUser();
        if (!$this->getGroupService()->isOwner($id, $user['id'])&& !$this->getGroupService()->isAdmin($id, $user['id'])  && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
            return $this->createMessageResponse('info', '您不是小组的创建者!');
        }
        
        $deleteMemberIds=$request->request->all();

        if(isset($deleteMemberIds['memberId'])){

             $deleteMemberIds=$deleteMemberIds['memberId'];

            foreach ($deleteMemberIds as $memberId) {
          
            $this->getGroupService()->deleteMemberByGroupIdAndUserId($id,$memberId);

            }
        }
        return new Response('success');
    }

    public function setAdminAction(Request $request,$id)
    {
        $user=$this->getCurrentUser();
        if (!$this->getGroupService()->isOwner($id, $user['id']) && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
            return $this->createMessageResponse('info', '您不是小组的创建者!');
        }
        
        $memberIds=$request->request->all();

        if(isset($memberIds['memberId'])){

             $memberIds=$memberIds['memberId'];

            foreach ($memberIds as $memberId) {
            
            $member=$this->getGroupService()->getMemberByGroupIdAndUserId($id,$memberId);
            $this->getGroupService()->updateMember($member['id'],array('role'=>'admin'));

            }
        }
        return new Response('success');

    }

    public function removeAdminAction(Request $request,$id)
    {
        $user=$this->getCurrentUser();
        if (!$this->getGroupService()->isOwner($id, $user['id']) && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
            return $this->createMessageResponse('info', '您不是小组的创建者!');
        }
        
        $memberIds=$request->request->all();

        if(isset($memberIds['adminId'])){

             $memberIds=$memberIds['adminId'];

            foreach ($memberIds as $memberId) {
            $member=$this->getGroupService()->getMemberByGroupIdAndUserId($id,$memberId);
            $this->getGroupService()->updateMember($member['id'],array('role'=>'member'));

            }
        }
        return new Response('success');

    }

    public function groupSetAction(Request $request,$id)
    {
        $user=$this->getCurrentUser();

        $group = $this->getGroupService()->getGroup($id);

        if (!$this->getGroupService()->isOwner($id, $user['id'])&& !$this->getGroupService()->isAdmin($id, $user['id']) && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
                return $this->createMessageResponse('info', '您不是小组的创建者或者小组已经关闭!');
        }

        return $this->render("TopxiaWebBundle:Group:setLogo.html.twig", array(
                    'groupinfo' => $group,
                    'is_groupmember' => $this->getGroupMemberRole($id),
                    'id'=>$id,
                    'logo'=>$group['logo'],
                    'backgroundLogo'=>$group['backgroundLogo'],)
                );

    }

    public function groupSetLogoCropAction(Request $request,$file,$id)
    {

        $group = $this->getGroupService()->getGroup($id);
        $currentUser = $this->getCurrentUser();

        if (!$this->getGroupService()->isOwner($id, $currentUser['id'])&& !$this->getGroupService()->isAdmin($id, $currentUser['id'])  && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
                return $this->createMessageResponse('info', '您不是小组的创建者或者小组已经关闭!');
        }
        $filename = $file;
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;
        
        if($request->getMethod() == 'POST') {

            $options = $request->request->all();
            if($request->query->get('page')=="backGroundLogoCrop"){
               $this->getGroupService()->changeGroupBackgroundLogo($id, $pictureFilePath, $options);
            }else{
               $this->getGroupService()->changeGroupLogo($id, $pictureFilePath, $options);
            }
          
        return $this->redirect($this->generateUrl('group_index', array(
                    'id'=>$id,
                    )));
        }
        try {

            $imagine = new Imagine(); 
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {          
            @unlink($pictureFilePath);
            return $this->createMessageResponse('info', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        if($request->query->get('page')=="backGroundLogoCrop"){
              $scaledSize = $naturalSize->widen(1070)->heighten(240);
        }else{
              $scaledSize = $naturalSize->widen(270)->heighten(270);
        }
      
        $pictureUrl = 'tmp/' . $filename;

        return $this->render('TopxiaWebBundle:Group:setLogoCrop.html.twig',array(
            'groupinfo' => $group,
            'is_groupmember' => $this->getGroupMemberRole($id),
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,));

    }
    private function setLogo($request,$user)
    {
        $data = $request->files->get('form');
        $file = $data['avatar'];

        if (!FileToolkit::isImageFile($file)) {
              return $this->createMessageResponse('info', '上传图片格式错误，请上传jpg, gif, png格式的文件。!');
        }
        $filenamePrefix = "user_{$user['id']}_";
        $hash = substr(md5($filenamePrefix . time()), -8);
        $ext = $file->getClientOriginalExtension();
        $filename = $filenamePrefix . $hash . '.' . $ext;
        $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
        $file = $file->move($directory, $filename);

        $fileName = str_replace('.', '!', $file->getFilename()); 

        return $filename;

    }

    public function setGroupLogoAction(Request $request, $id)
    {
        $user=$this->getCurrentUser();
        if (!$this->getGroupService()->isOwner($id, $user['id']) && !$this->getGroupService()->isAdmin($id, $user['id']) && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
        return $this->createMessageResponse('info', '您不是小组的创建者或者小组已经关闭!');
        }

        if ($request->getMethod() == 'POST') {

        $fileName=$this->setLogo($request,$user);

        return $this->redirect($this->generateUrl('group_setLogoCrop', array(
        'file' => $fileName,
        'id'=>$id,
        'page'=>'logoCrop',
        'action'=>'setLogo'
        )
        ));
        }

    }
     public function setGroupBackgroundLogoAction(Request $request,$id)
     {
        $user=$this->getCurrentUser();
        if (!$this->getGroupService()->isOwner($id, $user['id'])&& !$this->getGroupService()->isAdmin($id, $user['id'])  && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
            return $this->createMessageResponse('info', '您不是小组的创建者或者小组已经关闭!');
        }
        if ($request->getMethod() == 'POST') {

        $fileName=$this->setLogo($request,$user);

            return $this->redirect($this->generateUrl('group_setLogoCrop', array(
            'file' => $fileName,
            'id'=>$id,
            'page'=>'backGroundLogoCrop',
            'action'=>'setBackGroundLogo'
            )
            ));       
        }

    }
    
    public function getGroupMemberRole($userId)
    {
       $user = $this->getCurrentUser();

       if (!$user['id']) return 0;

       if ($this->getGroupService()->isOwner($userId, $user['id'])) return 2;

       if ($this->getGroupService()->isAdmin($userId, $user['id'])) return 3;

       if ($this->getGroupService()->isMember($userId, $user['id'])) return 1;

       return 0;
    }

    public function groupJoinAction($id) 
    {   
        $user=$this->getCurrentUser();
        $this->getGroupService()->joinGroup($user,$id);
        
        return $this->redirect($this->generateUrl('group_index', array(
                    'id'=>$id,
                    )));
    }
  
    public function groupExitAction($id)
    {
        $user=$this->getCurrentUser();
        $this->getGroupService()->exitGroup($user,$id);

        return $this->redirect($this->generateUrl('group_index', array(
                    'id'=>$id,
                    )));
    }

    public function groupEditAction(Request $request,$id)
    {
        $currentUser = $this->getCurrentUser();
        if (!$this->getGroupService()->isOwner($id, $currentUser['id']) && !$this->getGroupService()->isAdmin($id, $currentUser['id']) && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
                return $this->createMessageResponse('info', '您不是小组的创建者!');
        }
        $groupinfo=$request->request->all();
        $group=array();
        if($groupinfo){
              $group=array(
            'title'=>$groupinfo['group']['grouptitle'],
            'about'=>$groupinfo['group']['about']); 
        }        
        $this->getGroupService()->updateGroup($id,$group);
  
        return $this->redirect($this->generateUrl('group_index', array(
                    'id'=>$id,
                    )));
    }
   
    public function transferGroupAction(Request $request,$id)
    {
       $data=$request->request->all();
      
       $currentUser = $this->getCurrentUser();
        if (!$this->getGroupService()->isOwner($id, $currentUser['id']) && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
                return $this->createMessageResponse('info', '您不是小组的创建者!');
       }

       if($this->getUserService()->verifyPassword($currentUser['id'],$data['user']['password'])){

        $user=$this->getUserService()->getUserByNickname($data['user']['nickname']);

        $this->getGroupService()->updateGroup($id,array('ownerId'=>$user['id']));

        $member=$this->getGroupService()->getMemberByGroupIdAndUserId($id,$currentUser['id']);

        $this->getGroupService()->deleteMemberByGroupIdAndUserId($member['id'],$user['id']);
        $this->getGroupService()->updateMember($member['id'],array('userId'=>$user['id']));

       return $this->redirect($this->generateUrl('group_index', array(
                    'id'=>$id,
                    )));
       }

       $this->setFlashMessage('danger', '密码不正确，请重试。');
       return $this->redirect($this->generateUrl('group_set', array(
                    'id'=>$id,
                    'action'=>'transferGroup',
                    )));

    }
    private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    private function getGroupService() 
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
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
                    array('isStick','DESC'),
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
    private function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['type'] = $request->query->get('type');
        if (!in_array($filters['type'], array('all','elite'))) {
            $filters['type'] = 'all';
        }
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('byCreatedTime', 'byLastPostTime', 'byPostNum'))) {
            $filters['sort'] = 'byCreatedTime';
        }
        $filters['num'] = $request->query->get('num');

        if (!in_array($filters['num'], array(25))) {
            $filters['num'] = 25;
        }
        return $filters;
    }

    private function convertFiltersToConditions($id, $filters)
    {
        $conditions = array('groupId' => $id,'num'=>10,'enum'=>'open');
        switch ($filters['type']) {
            case 'elite':
                $conditions['isElite'] = 1;
                break;
            default:
                break;
        }
        $conditions['num'] = $filters['num'];
        return $conditions;
    }
}
