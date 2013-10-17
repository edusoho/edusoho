<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Component\OAuthClient\SaeTClientV2;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\TimeUtils;
use Topxia\WebBundle\Form\ActivityMemberType;

use Topxia\WebBundle\Form\QustionType;
use Topxia\WebBundle\Form\ActivitypostType;

class ActivityController extends BaseController
{

	public function exploreAction(Request $request)
    {
        $currentuser=$this->getCurrentUser();
        $filters = $this->getThreadSearchFilters($request);
        $conditions = $this->convertFiltersToConditions($filters);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getActivityService()->searchActivityCount($conditions)
            , 5
        );
        //活动
        $activitys = $this->getActivityService()->searchActivitys(
            $conditions, 'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        //地址
        $Locations=$this->getLocationService()->getAllLocations();
        //tag
        $tags = $this->getTagService()->findAllTags(0, 100);

        //我参加的活动
        $userId=$currentuser['id'];
    

        $actIds = ArrayToolkit::column($activitys,'id');


        $joinedActivitys=$this->getActivityService()->findMemberByActIds($actIds,$userId);


        $joinActIds = ArrayToolkit::column($joinedActivitys,'activityId');

      
        $mixActivitys= $this->getActivityService()->mixActivitys($activitys,$joinActIds);


        return $this->render('TopxiaWebBundle:Activity:explore.html.twig', array(
            'activitys' => $mixActivitys,
            'joinActIds'=>$joinActIds,
            'paginator' => $paginator,
            'conditions' => $conditions,
            'tags' => $tags,
            "current_user"=> $currentuser,
            'filters'=>$filters,
            'locations'=>$Locations,
        ));
    }

	public function showAction(Request $request, $id)
    {
        //活动信息
        $activity=$this->getActivityService()->getActivity($id);
        ///获取当前学生报名的活动ids
        $currentuser=$this->getCurrentUser();

        $activity= $this->getActivityService()->mixActivity($activity,$currentuser['id']);

        if($activity['expired']==1)
        {
            return $this->redirect($this->generateUrl("activity_expired",array(
                    "id"=>$id))
            );

        }
        //tag信息
        $tags = $this->getTagService()->findAllTags(0, 100);
        //报名的学生
        $students=$this->getActivityService()->findActivityStudents($id,0,50);
        $studentIds=ArrayToolkit::column($students,'userId');
        $students = $this->getUserService()->findUsersByIds($studentIds);
        
        
        //老师信息查询

        //小伙伴们正在看的活动
        $activitys=$this->getActivityService()->searchActivitys(array('status'=>'published'),'latestCreated',0,4);
        //活动的问题
        $threads=$this->getActivityThreadService()->findThreadsByType($activity['id'],'latestCreated',0,100);

        //问题回答ids
        $postUserIds=array();
        //问题回答
        $sss=array();
        foreach ($threads as $thread) {
            $thread['bindpost']=$this->getActivityThreadService()->findThreadPosts($activity['id'],$thread['id'],"default",0,20);
            if(!empty($thread['bindpost'])){
                $postUserIds=array_merge($postUserIds,ArrayToolkit::column($thread['bindpost'],'userId'));
            }
            $sss[]=$thread;
        }
        $qustionUserIds=ArrayToolkit::column($sss,'userId');
        $qustionUsers = $this->getUserService()->findUsersByIds($qustionUserIds);
       
        $qustionUsers[0]=array(
            "id" =>  0,
          "nickname" =>"游客");

      
        
        $postUsers = $this->getUserService()->findUsersByIds($postUserIds);
        $postUsers[0]=array(
            "id" =>  0,
          "nickname" =>"游客");

        return $this->render("TopxiaWebBundle:Activity:show-activity.html.twig",array(
            "activity"=>$activity,
            "tags"=>$tags,
            "students"=>$students,
            
            "qustions"=>$sss,
            "activitys"=>$activitys,
            "current_user"=> $currentuser,
            "qustion_users"=>$qustionUsers,
            "post_users"=>$postUsers));
    }
  

    public function expiredAction(Request $request,$id){

        $activity=$this->getActivityService()->getActivity($id);
        
        ///获取当前学生报名的活动ids
        $currentuser=$this->getCurrentUser();

        $activity= $this->getActivityService()->mixActivity($activity,$currentuser['id']);


        $threads=$this->getActivityThreadService()->findThreadsByType($activity['id'],'latestCreated',0,100);
        
        $activitys=$this->getActivityService()->searchActivitys(array('status'=>'published'),'latestCreated',0,4);
        
        $files=array();
        if(!empty($activity['photoId'])){
            $files=$this->getPhotoService()->searchFiles(array('groupId'=>$activity['photoId'][0]),'    latest',0,100);
        }

        $lession=array();
        if(!empty($activity['courseId'])){
            $lessons=$fristlessonid=$this->getCourseService()->getCourseLessons($activity['courseId'][0]);
           
            $lession=count($lessons)>0?$lessons[0]:array();
        }
       

        $studentIds = array();


        $students=$this->getActivityService()->findActivityStudents($id,0,50);
        foreach ($students as $key) {
            $studentIds[]=$key['userId'];
        }
        $students = $this->getUserService()->findUsersByIds($studentIds);

        $attachments=$this->getMaterialService()->findActivityMaterials($activity['id'],0,100);

        //问题回答ids
        $postUserIds=array();

        $sss=array();
        foreach ($threads as $thread) {
            $thread['bindpost']=$this->getActivityThreadService()->findThreadPosts($activity['id'],$thread['id'],"default",0,20);
            if(!empty($thread['bindpost'])){
                $postUserIds=array_merge($postUserIds,ArrayToolkit::column($thread['bindpost'],'userId'));
            }
            $sss[]=$thread;
        }

        $qustionUserIds=ArrayToolkit::column($sss,'userId');
        $qustionUsers = $this->getUserService()->findUsersByIds($qustionUserIds);
        $qustionUsers[0]=array(
            "id" =>  0,
          "nickname" =>"游客");
        
        $postUsers = $this->getUserService()->findUsersByIds($postUserIds);
        $postUsers[0]=array(
            "id" =>  0,
          "nickname" =>"游客");

        //附件下载
        $appendix=array();
        if(!empty($activity['id'])){
            $appendix=$this->getMaterialService()->findActivityMaterials($activity['id'],0,2);
        }

        return $this->render("TopxiaWebBundle:Activity:show-expired-activity.html.twig",array(
            "activity"=>$activity,
            "activitys"=>$activitys,
            "qustions"=>$sss,
            "pics"=>$files,
            "students"=>$students,
            'lesson'=>$lession,
            'attachments'=>$attachments,
            "current_user"=> $currentuser,
            "qustion_users"=>$qustionUsers,
            "post_users"=>$postUsers,
            "appendixs"=>$appendix));
    }


    public function headerAction($activity, $manage = false)
    {
        $user = $this->getCurrentUser();

        $users = empty($activity['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($course['teacherIds']);

        return $this->render('TopxiaWebBundle:Activity:header.html.twig', array(
            'activity' => $activity,
            'users' => $users,
            'manage' => $manage,
        ));
    }

    public function expertersBlockAction($activity)
    {
        $users = $this->getUserService()->findUsersByIds($activity['experterId']);
        $profiles = $this->getUserService()->findUserProfilesByIds($activity['experterId']);

        return $this->render('TopxiaWebBundle:Activity:experters-block.html.twig', array(
            'activity' => $activity,
            'users' => $users,
            'profiles' => $profiles,
        ));
    }


    public function successAction(Request $request,$id){
       
        $user=$this->getCurrentUser();
        
        $activity=$this->getActivityService()->getActivity($id);

        $isNew = $request->query->get('isNew');

        $hash=$this->makeHash($user);

        return $this->render("TopxiaWebBundle:Activity:join-activity-success.html.twig",array(
            "activityid"=>$id,
            "isNew"=>$isNew,
            "user"=>$user,
            "activity"=>$activity,
            "hash"=>$hash)
        );
    }
    
    public function joinAction(Request $request,$id)
    {  
        $user = $this->getCurrentUser();
        $activity=$this->getActivityService()->getActivity($id);
        $activity = $this->getActivityService()->mixActivity($activity,$user['id']);

        if( $activity['join']){

            return $this->redirect($this->generateUrl("activity_show",array(
                "id"=>$id))
            );
        }

        if ($request->getMethod() == 'POST') {
            $form = $this->createForm(new ActivityMemberType());
            $form->bind($request);
            $member = $form->getData();
            if (empty($user['id'])) {
                // regitser 
                $newuser['email']=$member['email'];
                $newuser['nickname']=$member['nickname'];
                //$newuser['password']=$this->getUserService()->createRandomPassworld();
                $newuser['password']='y**7^ian91!@MWSK';
                $newuser['createdIp'] = $request->getClientIp();

                $user=$this->getUserService()->register($newuser);

                $this->authenticateUser($user);

                $this->getNotificationService()->notify($user['id'], "default", $this->getWelcomeBody($user));

                $userprofile['id']=$user['id'];
                $userprofile['truename']=$member['truename'];
                $userprofile['mobile']= $member['mobile'];
                $userprofile['job']=$member['job'];
                $userprofile['company']=$member['company'];

                $this->getUserService()->updateUserProfile($user['id'],$userprofile);

                $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'));
               
                $this->sendActivaEmail($token,$user);




                $member['activityId']=$id;
                $member['userId']=$user['id'];

                $this->getActivityService()->addMeberByActivity($member);

                $this->getActivityService()->addActivityStudentNum($id);

                if(!empty($member['question'])){
                    $activity_thread['content']=$member['question'];
                    $activity_thread['activityId']=$id;
                    $this->getActivityThreadService()->createThread($activity_thread);  
                }

                return $this->redirect($this->generateUrl("activity_join_success",array(
                    "id"=>$id,
                    "isNew"=>true))
                );
              
            }else{ 

                $userprofile['id']=$user['id'];
                $userprofile['truename']=$member['truename'];
                $userprofile['mobile']= $member['mobile'];
                $userprofile['job']=$member['job'];
                $userprofile['company']=$member['company'];

                $this->getUserService()->updateUserProfile($user['id'],$userprofile);


                $member['activityId']=$id;
                $member['userId']=$user['id'];
                
                $this->getActivityService()->addMeberByActivity($member);
                $this->getActivityService()->addActivityStudentNum($id);
                if(!empty($member['question'])){
                    $activity_thread['content']=$member['question'];
                    $activity_thread['activityId']=$id;
                    $this->getActivityThreadService()->createThread($activity_thread);  
                }
                return $this->redirect($this->generateUrl("activity_join_success",array(
                    "id"=>$id,
                    "isNew"=>false))
                );  
                        
            }
        }

        $filename="join-activity-form-vistor";       

        $userprofile=array();
        if(!empty($user['id'])){
            $userprofile=$this->getUserService()->getUserProfile($user['id']);
            $filename="join-activity-form-member";
        }
        return $this->render("TopxiaWebBundle:Activity:".$filename.".html.twig",array(
            "activity"=>$activity,
            "user"=>$user,
            "profile"=>$userprofile)
        );
        
    }

    public function removeAction(Request $request,$id){
        $user = $this->getCurrentUser();
        if (empty($user['id'])) {
            throw $this->createAccessDeniedException();
        }
        $result=$this->getActivityService()->removeMember($id,$user['id']);
        if ($result>0) {
            $this->getActivityService()->reduceActivityStudentNum($id);
            return $this->redirect($this->generateUrl("activity_show",array(
                'id' => $id))
            );
        }   
        return $this->redirect($this->generateUrl("activity_show",array('id'=>$id
            ))
        );
    }

    private function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['istimeout'] = $request->query->get('istimeout');
        if (!in_array($filters['istimeout'], array('1','0' ))) {
            $filters['istimeout']=0;
        }

        $filters['status']='published';

        $filters['locationId'] = $request->query->get('location');
    
        $filters['tagId'] = $request->query->get('tagsid');
    
        $filters['time'] = $request->query->get('time');
        if (!in_array($filters['time'], array('thisweek', 'lastweek','nextweek','thismonth','lastmonth','nextmonth'))) {
        }

        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('created', 'posted', 'createdNotStick', 'postedNotStick'))) {
            $filters['sort'] = 'posted';
        }
        return $filters;
    }

    
    private function convertFiltersToConditions($filters)
    {
        $conditions = array();
        switch ($filters['status']) {
            case 'published':
                $conditions['status'] = 'published';
                break;
            case 'closed':
                $conditions['status'] = 'closed';
                break;
            default:
                break;
        }
        if(!empty($filters['locationId'])){
            $conditions['locationId']=$filters['locationId'];
        }
        if(!empty($filters['tagId'])){
            $conditions['tagId']=$filters['tagId'];
        }

        $startTime=0;
        $endTime=0;
        switch ($filters['time']) {
            case 'thisweek':
                    $startTime=TimeUtils::getThisWeekStartTime();
                    $endTime=TimeUtils::getThisweekEndTime();
                break;
            case 'lastweek':
                    $startTime=TimeUtils::getLastWeekStartTime();
                    $endTime=TimeUtils::getLastWeekEndTime();
                break;
            case 'nextweek':
                    $startTime=TimeUtils::getNextWeekStartTime();
                    $endTime=TimeUtils::getNextWeekEndTime();
                break;
            case 'thismonth':
                    $startTime=TimeUtils::getThisMonthStartTime();
                    $endTime=TimeUtils::getThisMonthEndTime();
                break;
            case 'lastmonth':
                    $startTime=TimeUtils::getLastMonthStartTime();
                    $endTime=TimeUtils::getLastMonthEndTime();
                break;
            case 'nextmonth':
                    $startTime=TimeUtils::getNextMonthStartTime();
                    $endTime=TimeUtils::getNextMonthEndTime();
                break;
            default:
                break;
        }
        if($startTime!=0){
            $conditions['startTimeGreaterThan']=$startTime;
        }
        if($endTime!=0){
            $conditions['startTimeLessThan']=$endTime;    
        }
        
        

        if($filters['istimeout']!=null){
            $conditions['istimeout']=$filters['istimeout'];
        }

        return $conditions;
    }


    public function qustioncreateAction(Request $request,$id){        

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(new QustionType());

        if ($request->getMethod() == 'POST') {
            $form->bind($request); 
                $fields = $form->getData();
                $fields['activityId']= $id;
                $this->sendWeibo($fields['castweibo'],$fields);
                unset($fields['castweibo']);
                $qustion=$this->getActivityThreadService()->createThread($fields);
                $url = $this->get('router')->generate('activity_qustionpost', array('id' =>$qustion['activityId'] ,'qid'=> $qustion['id']));
                $qustion['action']=$url;
                $currentuser=$this->getCurrentUser();
                
                if(empty($qustion['userId'])){
                    $qustion['usernickname']="游客";
                    $qustion['usersmallAvatar']="/assets/img/default/avatar.png";
                }else{
                    $qustion['usernickname']=$currentuser['nickname'];
                    $qustion['usersmallAvatar']=$this->getWebExtension()->getFilePath($currentuser['smallAvatar']);
                }
                $newqustion=$qustion; 
                return $this->render('TopxiaWebBundle:Activity:qustion-create.html.twig', array(
                    'qustion'=>$newqustion
                ));
        }
        
    }

    private function sendWeibo($castweibo,$fields){
        if($castweibo){
            $currentuser=$this->getCurrentUser();
            if(!empty($currentuser['id'])){
                $binduser=$this->getUserService()->getUserBindByTypeAndUserId("weibo",$currentuser['id']);
                $token=$binduser['token'];
                if(!empty($token)){
                    $c = new SaeTClientV2("2639729631","fe8353fc845437c64eb1107d5ba76f38", $token);
                    $ms  = $c->home_timeline();
                    $ret = $c->update($fields['content']);
                }
            }
        }

    }

    public function createAction(Request $request)
    {
        $form = $this->createActivityForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $course = $form->getData();
                $course = $this->getActivityService()->createActivity($course);
                return $this->redirect($this->generateUrl('activity_manage', array('id' => $course['id'])));
            }
        }

        return $this->render('TopxiaWebBundle:Activity:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    private function createActivityForm()
    {
        return $this->createNamedFormBuilder('activity')
            ->add('title', 'text')
            ->getForm();
    }

    public function postcreateAction(Request $request,$id,$qid){
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(new ActivitypostType());
        if ($request->getMethod() == 'POST') {
                $form->bind($request);
                $fields = $form->getData();
                $this->sendWeibo(true,$fields);
                $fields['activityId']= $id;
                $fields['threadId']=$qid;
                //$fields['content']="zhaoxin test";
                $post=$this->getActivityThreadService()->postThread($fields);
                $currentuser=$this->getCurrentUser();
                if(empty($currentuser['id'])){
                    $post['usernickname']="游客";
                }else{
                    $post['usernickname']=$currentuser['nickname'];
                }
                $newpost=$post;
                return $this->render('TopxiaWebBundle:Activity:qustion-post-create.html.twig', array(
                    'post'=>$newpost
                ));
            }
        return $this->createJsonResponse(false);
    }

    public function zhaoxinAction(Request $request){
       // $currentuser=$this->getCurrentUser();
        //$binduser=$this->getUserService()->getUserBindByTypeAndUserId("weibo",$currentuser['id']);
        ///$token=$binduser['token'];
        //$c = new SaeTClientV2("2639729631","fe8353fc845437c64eb1107d5ba76f38",$token);
        //$ms  = $c->home_timeline();
        //$uid_get = $c->get_uid();
        //$uid = $uid_get['uid'];
        //$user_message = $c->show_user_by_id( $uid);

        $ret = $c->update("Hello wrold");
        if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
            return $this->createJsonResponse(false);   
        } else {
            return $this->createJsonResponse(true);   
        }

        ///$ret = $c->update("Hello wrold");
        //if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
         //   return $this->createJsonResponse(false);   
        //} else {
         //   return $this->createJsonResponse(true);   
        //}
        return $this->render('TopxiaWebBundle:Activity:show-success.html.twig');
    }

   

    private function sendActivaEmail($token, $user)
    {
        $this->sendEmail(
                $user['email'],
                "欢迎参加开源力量公开课，请激活您的账号并初始化密码",
                $this->renderView('TopxiaWebBundle:Activity:send-email.html.twig', array(
                    'user' => $user,
                    'token' => $token,
                )), 'html'
        );
    }


    private function makeHash($user)
    {
        $string = $user['id'] . $user['email'] . $this->container->getParameter('secret');
        return md5($string);
    }

    private function checkHash($userId, $hash)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return false;
        }

        if ($this->makeHash($user) !== $hash) {
            return false;
        }

        return $user;
    }

    private function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity.ActivityService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
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
    private function getActivityThreadService()
    {
        return $this->getServiceKernel()->createService('Activity.ThreadService');
    }

    private function getCourseService(){
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }


    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    private function getWelcomeBody($user)
    {
        $auth = $this->getSettingService()->get('auth', array());
        $site = $this->getSettingService()->get('site', array());
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}');
        $valuesToReplace = array($user['nickname'], $site['name'], $site['url']);
        $welcomeBody = $this->setting('auth.welcome_body', '注册欢迎内容');
        $welcomeBody = str_replace($valuesToBeReplace, $valuesToReplace, $welcomeBody);
        return $welcomeBody;
    }


}




