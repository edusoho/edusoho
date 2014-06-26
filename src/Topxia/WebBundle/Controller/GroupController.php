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
            $mycreatedGroup = $this->getGroupService()->searchGroups(array('ownerId'=>$user['id']),array('createdTime','DESC'), 0, 9);
            $myJoinGroup =  $this->getGroupService()->findGroupsByUserId($user['id']);
            $myJoinGroup=array_slice($myJoinGroup,-9);
        }
        return $this->render("TopxiaWebBundle:Group:index.html.twig", array(
                    'activegroup' => $activeGroup,
                    'mycreatedGroup' => $mycreatedGroup,
                    'myjionGroup' => $myJoinGroup,
                    'owner'=>$owner,
                    'groupinfo'=>$groups,
                    'thread_recentlyinfo'=>$recentlyThread,
        ));
    }

    public function addGroupAction(Request $request) 
    {
        $user = $this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $mygroup = $request->request->all();
            $group = array(
                'title' => $mygroup['group']['grouptitle'],
                'about' => $mygroup['group']['about'],
                'ownerId' => $user['id'],
                'memberNum' => 1,
                'createdTime' => time(),
            );
            $group = $this->getGroupService()->addGroup($group);
            return $this->redirect($this->generateUrl('group_index',array('id'=>$group['id'])));
        }

        return $this->render("TopxiaWebBundle:Group:groupadd.html.twig");
    }

    public function groupIndexAction(Request $request,$id) 
    {
        $group = $this->getGroupService()->getGroup($id);

        if($group['enum']=="close"){
            return $this->createMessageResponse('error','该小组已被关闭');
        }

        $recentlyJoinMember=$this->getGroupService()->searchMembers(array('groupId'=>$id,'role'=>'member'),array('createdTime','DESC'),0,12);
        $recentlyThread = $this->getThreadService()->searchThreads(array('groupId'=>$id,'enum'=>'open'),$this->filterSort('byStick'), 0, 15);
        
        $memberIds = array_merge(
            ArrayToolkit::column($recentlyJoinMember, 'userId')
        );
        $ownerIds = array_merge(
            ArrayToolkit::column($recentlyThread, 'userId')
        );
        $userIds = array_merge(
            ArrayToolkit::column($recentlyThread, 'lastPostMemberId')
        );

        $owner=$this->getUserService()->findUsersByIds($ownerIds);
        $lastPostMembers=$this->getUserService()->findUsersByIds($userIds);
        $members=$this->getUserService()->findUsersByIds($memberIds);

        return $this->render("TopxiaWebBundle:Group:groupindex.html.twig", array(
                    'groupinfo' => $group,
                    'is_groupmember' => $this->getGroupMemberRole($id),
                    'groupmember_recentlyinfo'=>$recentlyJoinMember,
                    'thread_recentlyinfo'=>$recentlyThread,
                    'owner'=>$owner,
                    'latedinfo'=>$lastPostMembers,
                    'members'=>$members,
                   
        ));
    }

     public function groupMemberAction(Request $request,$id) 
     {
        $group = $this->getGroupService()->getGroup($id);

        if($group['enum']=="close"){
            return $this->createMessageResponse('error','该小组已被关闭');
        }
        $user=$this->getCurrentUser();

        if($request->getMethod()=="POST"){

             if (!$this->getGroupService()->isOwner($id, $user['id'])) {
                return $this->createMessageResponse('error', '您不是小组的创建者!');
             }
            
            // @todo 新开action deleteMembersAction

            $deleteMemberIds=$request->request->all();

            if(isset($deleteMemberIds['deleteMemberId'])){

                 $deleteMemberIds=$deleteMemberIds['deleteMemberId'];

                foreach ($deleteMemberIds as $memberId) {
              
                $this->getGroupService()->deleteMemberByGroupIdAndUserId($memberId,$id);

                }
            }
        }
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

        $members=$this->getUserService()->findUsersByIds($memberIds);
        $owner=$this->getUserService()->getUser($group['ownerId']);

        return $this->render("TopxiaWebBundle:Group:groupmember.html.twig", array(
                    'groupinfo' => $group,
                    'is_groupmember' => $this->getGroupMemberRole($id),
                    'groupmember_info'=>$Members,
                    'owner_info'=>$owner,
                    'paginator'=>$paginator,
                    'members'=>$members,

        ));
    }

    public function setGroupLogoAction(Request $request, $id)
    {
        $user=$this->getCurrentUser();

        if (!$this->getGroupService()->isOwner($id, $user['id'])) {
                return $this->createMessageResponse('error', '您不是小组的创建者或者小组已经关闭!');
        }

        // @todo FormBuilder  不要再使用
        $form = $this->createFormBuilder()
            ->add('avatar', 'file')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $file = $data['avatar'];

                if (!FileToolkit::isImageFile($file)) {
                     $this->setFlashMessage('danger', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
                     return $this->render('TopxiaWebBundle:MyGroup:setgrouplogo.html.twig',array(
                     'form' => $form->createView(),));
                }

                $filenamePrefix = "user_{$user['id']}_";
                $hash = substr(md5($filenamePrefix . time()), -8);
                $ext = $file->getClientOriginalExtension();
                $filename = $filenamePrefix . $hash . '.' . $ext;
                $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
                $file = $file->move($directory, $filename);

                $fileName = str_replace('.', '!', $file->getFilename());  
                return $this->redirect($this->generateUrl('grouplogo_crop_set', array(
                    'file' => $fileName,
                    'id'=>$id,
                    )
                ));
            }
        }

        $groupinfo=$this->getGroupService()->getGroup($id);

        return $this->render('TopxiaWebBundle:Group:setgrouplogo.html.twig',array(
             'form' => $form->createView(),
             'id'=>$id,
             'logo'=>$groupinfo['logo'],));
    }

     public function setGroupBackgroundLogoAction(Request $request,$id)
     {
        $user=$this->getCurrentUser();
        if (!$this->getGroupService()->isOwner($id, $user['id'])) {
                return $this->createMessageResponse('error', '您不是小组的创建者或者小组已经关闭!');
        }

        //@todo
        $form = $this->createFormBuilder()
            ->add('avatar', 'file')
            ->getForm();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $file = $data['avatar'];

                if (!FileToolkit::isImageFile($file)) {
                     $this->setFlashMessage('danger', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
                     return $this->render('TopxiaWebBundle:Group:setgroupbackgroundlogo.html.twig',array(
                     'form' => $form->createView(),));
                }

                $filenamePrefix = "user_{$user['id']}_";
                $hash = substr(md5($filenamePrefix . time()), -8);
                $ext = $file->getClientOriginalExtension();
                $filename = $filenamePrefix . $hash . '.' . $ext;
                $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
                $file = $file->move($directory, $filename);

                $fileName = str_replace('.', '!', $file->getFilename());  
                return $this->redirect($this->generateUrl('groupbackgroundlogo_crop_set', array(
                    'file' => $fileName,
                    'id'=>$id,
                    )
                ));
            }
        }
        $groupinfo=$this->getGroupService()->getGroup($id);

        return $this->render('TopxiaWebBundle:Group:setgroupbackgroundlogo.html.twig',array(
             'form' => $form->createView(),
             'id'=>$id,
             'logo'=>$groupinfo['backgroundLogo'],));
    }

    public function setGroupBackgroundLogo_cropAction(Request $request,$file,$id)
    {

        $currentUser = $this->getCurrentUser();

        if (!$this->getGroupService()->isOwner($id, $currentUser['id'])) {
                return $this->createMessageResponse('error', '您不是小组的创建者或者小组已经关闭!');
        }
        $filename = $file;
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $this->getGroupService()->changeGroupBackgroundLogo($id, $pictureFilePath, $options);
          
        return $this->redirect($this->generateUrl('group_index', array(
                    'id'=>$id,
                    )));
        }
        try {

            $imagine = new Imagine(); 
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {          
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(1070)->heighten(240);
        $pictureUrl = 'tmp/' . $filename;
        return $this->render('TopxiaWebBundle:Group:setgroupbackgroundlogo_crop.html.twig',array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,));

    }
    public function setGroupLogo_cropAction(Request $request,$file,$id)
    {

        $currentUser = $this->getCurrentUser();

        if (!$this->getGroupService()->isOwner($id, $currentUser['id'])) {
                return $this->createMessageResponse('error', '您不是小组的创建者或者小组已经关闭!');
        }
        $filename = $file;
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            
            $options = $request->request->all();
            $this->getGroupService()->changeGroupLogo($id, $pictureFilePath, $options);
           

        return $this->redirect($this->generateUrl('group_index', array(
                    'id'=>$id,
                    )));
        }
        try {

            $imagine = new Imagine(); 
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {          
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(270)->heighten(270);
        $pictureUrl = 'tmp/' . $filename;
        return $this->render('TopxiaWebBundle:Group:setgrouplogo_crop.html.twig',array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,));

    }

    public function getGroupMemberRole($userId)
    {
       $user = $this->getCurrentUser();

       if (!$user['id']) return 0;

       if ($this->getGroupService()->isOwner($userId, $user['id'])) return 2;

       if ($this->getGroupService()->isMember($userId, $user['id'])) return 1;

       return 0;
    }

    public function groupJoinAction($id) 
    {
        $this->getGroupService()->joinGroup($id);
        
        return $this->redirect($this->generateUrl('group_index', array(
                    'id'=>$id,
                    )));
    }
  
    public function groupExitAction($id)
    {
        $this->getGroupService()->exitGroup($id);

        return $this->redirect($this->generateUrl('group_index', array(
                    'id'=>$id,
                    )));
    }

    public function groupEditAction(Request $request,$id)
    {
        $currentUser = $this->getCurrentUser();
        if (!$this->getGroupService()->isOwner($id, $currentUser['id'])) {
                return $this->createMessageResponse('error', '您不是小组的创建者!');
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

    
    public function addThreadAction(Request $request,$id)
    {
        $user=$this->getCurrentUser();
        $groupinfo = $this->getGroupService()->getGroup($id);
        if(!$groupinfo){
            return $this->createMessageResponse('error','该小组已被关闭');
        }
        if(!$this->getGroupMemberRole($id)){
            return $this->createMessageResponse('error','只有小组成员可以发言');
        }

        if($this->getRequest()->getSession()->get('addThreadCount')>1){
              $checkCodeStatus=1;
        }else{

              $checkCodeStatus=0;
        }

        if($request->getMethod()=="POST"){
            $thread = $request->request->all();

            if(isset($thread['checkCode'])){
                if(!$this->checkCode($thread['checkCode'])){

                $this->setFlashMessage('danger', '验证码错误！');
                
                return $this->redirect($this->generateUrl('groupthread_add', array(
                    'id'=>$id,
                    )));
            }

        }
            $info=array(
                'title'=>$thread['thread']['title'],
                'content'=>$thread['thread']['content'],
                'groupId'=>$id,
                'createdTime'=>time(),
                'userId'=>$user['id']);
            $thread=$this->getThreadService()->addThread($info);
            

            if(!$this->getRequest()->getSession()->get('addThreadCount')){

                $this->getRequest()->getSession()->set('addThreadCount',1);
            }
 
            if($this->getRequest()->getSession()->get('addThreadTime')&&strtotime(date('Y-m-d H:i:s'))-strtotime($this->getRequest()->getSession()->get('addThreadTime')) <20){

                 $this->getRequest()->getSession()->set('addThreadCount',$this->getRequest()->getSession()->get('addThreadCount')+1);
            }

            $this->getRequest()->getSession()->set('addThreadTime',date('Y-m-d H:i:s'));

            return $this->redirect($this->generateUrl('groupthread_index', array(
                    'id'=>$id,
                    'threadid'=>$thread['id'],
                    )));
            
        }
        return $this->render('TopxiaWebBundle:Group:addthread.html.twig',array(
            'id'=>$id,
            'groupinfo'=>$groupinfo,
            'checkCodeStatus'=>$checkCodeStatus,
            'is_groupmember' => $this->getGroupMemberRole($id)));
    }
    
     public function groupThreadAction(Request $request, $id)
     {

        $groupinfo = $this->getGroupService()->getGroup($id);

        if($groupinfo['enum']=="close"){
            return $this->createMessageResponse('error','该小组已被关闭');
        }

        $filters = $this->getThreadSearchFilters($request);

        $conditions = $this->convertFiltersToConditions($id, $filters);  
    
        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->getThreadCount($conditions),
            $conditions['num']  
        );
            
        $threadinfo=$this->getThreadService()->searchThreads(
            $conditions,
            $this->filterSort($filters['sort']),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
            );

        $ownerIds = array_merge(
            ArrayToolkit::column($threadinfo, 'userId')
        );
        $groupIds = array_merge(
            ArrayToolkit::column($threadinfo, 'groupId')
        );

        $userIds = array_merge(
            ArrayToolkit::column($threadinfo, 'lastPostMemberId')
        );

        $owner=$this->getUserService()->findUsersByIds($ownerIds);

        $neargroupinfo=$this->getGroupService()->getGroupsByIds($groupIds);

        $latedinfo=$this->getUserService()->findUsersByIds($userIds);

        $groupmember_activeinfo=$this->getGroupService()->searchMembers(array('groupId'=>$id,'role'=>'member'),array('postNum','DESC'),0,12);

        $memberIds = array_merge(
            ArrayToolkit::column($groupmember_activeinfo, 'userId')
        );

        $members=$this->getUserService()->findUsersByIds($memberIds);

       return $this->render('TopxiaWebBundle:Group:groupthread.html.twig',array(
            'id'=>$id,
            'groupinfo'=>$groupinfo,
            'threadinfo'=>$threadinfo,
            'paginator'=>$paginator,
            'owner'=>$owner,
            'condition'=>$filters,
            'neargroupinfo'=>$neargroupinfo,
            'latedinfo'=>$latedinfo,
            'groupmember_activeinfo'=>$groupmember_activeinfo,
            'members'=>$members,
            'is_groupmember' => $this->getGroupMemberRole($id)));
    }
    public function groupThreadIndexAction(Request $request,$id,$threadid)
    {
        $groupinfo = $this->getGroupService()->getGroup($id);

        if($groupinfo['enum']=="close"){
            return $this->createMessageResponse('error','该小组已被关闭');
        }

        if($this->getRequest()->getSession()->get('postCount')>4){
        
              $checkCodeStatus=1;
        }else{

              $checkCodeStatus=0;
        }

        $user=$this->getCurrentUser();

        if($request->getMethod()=="POST"){

            if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
            }
            if(!$this->getGroupMemberRole($id)){

            $joinid = $this->getGroupService()->joinGroup($id);
            }

            $postContent=$request->request->all();

            if(isset($postContent['checkCode'])){

                if(!$this->checkCode($postContent['checkCode'])){
        
                return new Response('error');
                }
            }

            $content=array(
            'threadId'=>$threadid,
            'content'=>$postContent['content'],
            'userId'=>$user['id'],
            'createdTime'=>time());

            $this->getThreadService()->postThread($content,$id,$user['id'],$threadid);

            if(!$this->getRequest()->getSession()->get('postCount')){

                $this->getRequest()->getSession()->set('postCount',1);
            }
 
            //如果在20秒内连续回复了5次，之后需要验证码
            if($this->getRequest()->getSession()->get('postTime')&&strtotime(date('Y-m-d H:i:s'))-strtotime($this->getRequest()->getSession()->get('postTime')) <20){

                 $this->getRequest()->getSession()->set('postCount',$this->getRequest()->getSession()->get('postCount')+1);
            }

            $this->getRequest()->getSession()->set('postTime',date('Y-m-d H:i:s'));

            return new Response('sucess');
            
        }
        $threadMain=$this->getThreadService()->getThread($threadid);

        if(!$threadMain['enum']){
            return $this->createMessageResponse('error','该话题已被关闭');
        }

        $owner=$this->getUserService()->getUser($threadMain['userId']);

        $filters=$this->getPostSearchFilters($request);

        $condition=$this->getPostCondition($filters['type'],$threadMain['userId'],$threadid);

        $sort=$this->getPostOrderBy($filters['sort']);

        $postCount=$this->getThreadService()->searchPostsCount($condition);

        $paginator = new Paginator(
            $this->get('request'),
            $postCount,
            10  
        );
        $post=$this->getThreadService()->searchPosts($condition,$sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $postMemberIds = array_merge(
            ArrayToolkit::column($post, 'userId')
        );

        $postMember=$this->getUserService()->findUsersByIds($postMemberIds);

        $groupmember_activeinfo=$this->getGroupService()->searchMembers(array('groupId'=>$id,'role'=>'member'),array('postNum','DESC'),0,12);

        $groupCount=$this->getGroupService()->searchGroupsCount(array('enum'=>'open'));

        $start=$groupCount>12 ? rand(0,$groupCount-12) : 12 ;

        $deserveGroup=$this->getGroupService()->searchGroups(array('enum'=>'open',),array('createdTime','DESC'),$start,12);

        $memberIds = array_merge(
            ArrayToolkit::column($groupmember_activeinfo, 'userId')
        );
        $members=$this->getUserService()->findUsersByIds($memberIds);

        
        return $this->render('TopxiaWebBundle:Group:thread.html.twig',array(
        'groupinfo' => $groupinfo,
        'threadMain'=>$threadMain,
        'user'=>$user,
        'owner'=>$owner,
        'post'=>$post,
        'paginator'=>$paginator,
        'postMember'=>$postMember,
        'filters'=>$filters,
        'postCount'=>$postCount,
        'checkCodeStatus'=>$checkCodeStatus,
        'groupmember_activeinfo'=>$groupmember_activeinfo,
        'deserveGroup'=>$deserveGroup,
        'members'=>$members,
        'is_groupmember' => $this->getGroupMemberRole($id)));


    }

    private function checkCode($checkCode)
    {           
        if($this->getRequest()->getSession()->get('checkcode')!=$checkCode) return  0;
        return 1;
    }

    public function setEliteAction($threadId)
    {
        return $this->postAction($threadId,'setElite');
    }

    public function removeEliteAction($threadId)
    {
        return $this->postAction($threadId,'removeElite');
    }

    public function setStickAction($threadId)
    {
        return $this->postAction($threadId,'setStick');
    }

    public function removeStickAction($threadId)
    {
        return $this->postAction($threadId,'removeStick');
    }

    public function closeThreadAction($threadId,$memberId)
    {
        $thread=$this->getThreadService()->getThread($threadId);

        $groupMemberRole=$this->getGroupMemberRole($thread['groupId']);

        if($groupMemberRole!==2 && $thread['userId']!==$memberId ){

        return new Response($this->generateUrl('groupthread', array(
        'id'=>$thread['groupId'],

        )));

        }
        $this->getThreadService()->closeThread($threadId);

        return new Response($this->generateUrl('groupthread', array(
        'id'=>$thread['groupId'],
        )));     
    }


    public function deletePostAction($postId,$memberId,$threadOwnerId,$groupOwnerId,$groupId)
    {
        $post=$this->getThreadService()->getPost($postId);

        if($post['userId']!==$memberId && $memberId!==$threadOwnerId && $memberId!==$groupOwnerId && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true){
            
            return new Response($this->generateUrl('groupthread_index', array(
            'id'=>$groupId,'threadid'=>$post['threadId'],
        ))); 
        }
        $this->getThreadService()->deletePost($postId);

        return new Response($this->generateUrl('groupthread_index', array(
            'id'=>$groupId,'threadid'=>$post['threadId'],
        ))); 

    }


    private function postAction($threadId,$action)
    {
        $thread=$this->getThreadService()->getThread($threadId);

        $groupMemberRole=$this->getGroupMemberRole($thread['groupId']);

        if($groupMemberRole!==2){

        return $this->createMessageResponse('info', '您没有权限！');

        }
        if($action=='setElite'){
           $this->getThreadService()->setElite($threadId); 
        }
        if($action=='removeElite'){
           $this->getThreadService()->removeElite($threadId); 
        }
        if($action=='setStick'){
           $this->getThreadService()->setStick($threadId); 
        }
        if($action=='removeStick'){
           $this->getThreadService()->removeStick($threadId); 
        }

        return new Response($this->generateUrl('groupthread_index', array(
        'id'=>$thread['groupId'],
        'threadid'=>$threadId,
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

        if (!in_array($filters['num'], array(15))) {
            $filters['num'] = 15;
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
    private function getPostSearchFilters($request)
    {
        $filters=array();

        $filters['type']=$request->query->get('type');

        if(!in_array($filters['type'], array('all','onlyOwner'))){

            $filters['type']='all';
        }

        $filters['sort']=$request->query->get('sort');

        if(!in_array($filters['sort'], array('asc','desc'))){

            $filters['sort']='asc';
        }

        return $filters;
    }
    private function getPostCondition($filters,$ownId,$threadId)
    {
        if($filters=='all') return array('threadId'=>$threadId,'enum'=>'open');

        if($filters=='onlyOwner') return array('threadId'=>$threadId,'enum'=>'open','userId'=>$ownId);

        return false;

    }
    private function getPostOrderBy($sort)
    {
        if($sort=='asc') return array('createdTime','asc');

        if($sort=='desc') return array('createdTime','desc');
    }


    public function checkCodeAction($text=6) 
    {
    $response = new Response();
    $response->headers->set('Content-Type', 'image/png');
    $authnum=$this->random(6);//验证码字符.
    $this->getRequest()->getSession()->set('checkcode',$authnum);
    $im = imagecreate(100,28); //imagecreate() 新建图像，大小为 x_size 和 y_size 的空白图像。
    $red = ImageColorAllocate($im,166, 202, 240); //设置背景颜色
    $white = ImageColorAllocate($im,0, 0,0);//设置文字颜色
    $gray = ImageColorAllocate($im, 102,102,0); //设置杂点颜色
    imagefill($im,55,18,$red);

    for ($i = 0; $i < strlen($authnum); $i++)
    {
    imagestring($im, 5, 15*$i+12, 6, substr($authnum,$i,1), $white);
    }
    for($i=0;$i<400;$i++) //加入干扰象素
    {
    imagesetpixel($im, rand()%95 , rand()%98 , $gray);
   }
    ImagePNG($im); //以 PNG 格式将图像输出到浏览器或文件
    ImageDestroy($im);//销毁一图像
    return $response;
    }
    
    private function random($length) 
    {
            $hash = '';
            $chars = '123456789';

            $max = strlen($chars) - 1;

            for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
            }
            return $hash;

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

}
