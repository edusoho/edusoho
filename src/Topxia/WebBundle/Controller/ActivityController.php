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
        $userId=$currentuser['id'];


        //本期活动
        $recommendedActivitys = $this->getActivityService()->findRecommendedActivity();
        $recommendedActivitys =  $this->getActivityService()->extActivitys($recommendedActivitys);
        $recommendedActivitys= $this->getActivityService()->mixActivitys($recommendedActivitys,$userId);


        //近期活动
        $lastActivitys = $this->getActivityService()->findLastActivitys();
        $lastActivitys =  $this->getActivityService()->extActivitys($lastActivitys);
        $lastActivitys= $this->getActivityService()->mixActivitys($lastActivitys,$userId);


        //往期活动
        $conditions['status']='published';
        $conditions['actType']='公开课';
        $conditions['expired']='1';//1表示往期。
        $paginator = new Paginator(
            $this->get('request'),
            $this->getActivityService()->searchActivityCount($conditions)
            , 8
        ); 
        $expiredActivitys = $this->getActivityService()->searchActivitys(
            $conditions, 'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $expiredActivitys =  $this->getActivityService()->extActivitys($expiredActivitys);      
        $expiredActivitys= $this->getActivityService()->mixActivitys($expiredActivitys,$userId);


        return $this->render('TopxiaWebBundle:Activity:explore.html.twig', array(
            'recommendedActivitys' =>$recommendedActivitys,
            'lastActivitys' =>$lastActivitys,
            'expiredActivitys' => $expiredActivitys,          
            'paginator' => $paginator,         
            "current_user"=> $currentuser,
          
        ));
    }

    //显示公开课未结束的页面
	public function showAction(Request $request, $id)
    {
        //活动信息
        $activity=$this->getActivityService()->getActivity($id);
        ///获取当前学生报名的活动ids
        $currentuser=$this->getCurrentUser();

        $activity= $this->getActivityService()->extActivity($activity);

        $activity= $this->getActivityService()->mixActivity($activity,$currentuser['id']);

        // if($activity['expired']==1)
        // {
        //     return $this->redirect($this->generateUrl("activity_expired",array(
        //             "id"=>$id))
        //     );

        // }
       
        //报名的学生
        $students=$this->getActivityService()->findActivityStudents($id,0,20);
        $studentIds=ArrayToolkit::column($students,'userId');
        $students = $this->getUserService()->findUsersByIds($studentIds);
        
        
        //小伙伴们正在看的活动
        $activitys=$this->getActivityService()->searchActivitys(array('status'=>'published'),'latestCreated',0,4);
        //活动的问题
        $threads=$this->getActivityThreadService()->findThreadsByType($activity['id'],'latestCreated',0,100);

        //问题回答ids
        $postUserIds=array();
        //问题回答
        $threadPosts=array();
        foreach ($threads as $thread) {
            $thread['bindpost']=$this->getActivityThreadService()->findThreadPosts($activity['id'],$thread['id'],"default",0,20);
            if(!empty($thread['bindpost'])){
                $postUserIds=array_merge($postUserIds,ArrayToolkit::column($thread['bindpost'],'userId'));
            }
            $threadPosts[]=$thread;
        }
        $qustionUserIds=ArrayToolkit::column($threadPosts,'userId');

        $qustionUsers = $this->getUserService()->findUsersByIds($qustionUserIds);
      
        
        $postUsers = $this->getUserService()->findUsersByIds($postUserIds);
       

        //附件下载
        $appendix=array();
        if(!empty($activity['id'])){
            $appendix=$this->getMaterialService()->findActivityMaterials($activity['id'],0,10);
        }

        $pics=array();
        if(!empty($activity['photoId'])){
            $pics=$this->getPhotoService()->searchFiles(array('groupId'=>$activity['photoId'][0]),'latest',0,100);
        }

        $courses=array();
        if(!empty($activity['courseId'])){
            $courses=$this->getCourseService()->findCoursesByIds($activity['courseId']);

        }



        return $this->render("TopxiaWebBundle:Activity:show-activity.html.twig",array(
            "activity"=>$activity,
            "students"=>$students,            
            "qustions"=>$threadPosts,
            "activitys"=>$activitys,
            "current_user"=> $currentuser,
            "qustion_users"=>$qustionUsers,
            "post_users"=>$postUsers,
            "appendixs"=>$appendix,
            "pics"=>$pics,
            "courses"=>$courses)
        );
    }
  
   //显示公开课已结束的页面
    public function expiredAction(Request $request,$id){

        $activity=$this->getActivityService()->getActivity($id);
        
        ///获取当前学生报名的活动ids
        $currentuser=$this->getCurrentUser();

        $activity= $this->getActivityService()->extActivity($activity);

        $activity= $this->getActivityService()->mixActivity($activity,$currentuser['id']);


        $threads=$this->getActivityThreadService()->findThreadsByType($activity['id'],'latestCreated',0,100);
        
        $activitys=$this->getActivityService()->searchActivitys(array('status'=>'published'),'latestCreated',0,4);
        
        $files=array();
        if(!empty($activity['photoId'])){
            $files=$this->getPhotoService()->searchFiles(array('groupId'=>$activity['photoId'][0]),'latest',0,100);
        }

        $lession=array();
        if(!empty($activity['courseId'])){
            $lessons=$this->getCourseService()->getCourseLessons($activity['courseId'][0]);
           
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

        $threadPosts=array();
        foreach ($threads as $thread) {
            $thread['bindpost']=$this->getActivityThreadService()->findThreadPosts($activity['id'],$thread['id'],"default",0,20);
            if(!empty($thread['bindpost'])){
                $postUserIds=array_merge($postUserIds,ArrayToolkit::column($thread['bindpost'],'userId'));
            }
            $threadPosts[]=$thread;
        }

        $qustionUserIds=ArrayToolkit::column($threadPosts,'userId');
        $qustionUsers = $this->getUserService()->findUsersByIds($qustionUserIds);
        $qustionUsers[0]=array(
            "id" =>  0,
          "nickname" =>"游客");
        
        $postUsers = $this->getUserService()->findUsersByIds($postUserIds);
        $postUsers[0]=array(
            "id" =>  0,
          "nickname" =>"游客");

        

        return $this->render("TopxiaWebBundle:Activity:show-expired-activity.html.twig",array(
            "activity"=>$activity,
            "activitys"=>$activitys,
            "qustions"=>$threadPosts,
            "pics"=>$files,
            "students"=>$students,
            'lesson'=>$lession,
            'attachments'=>$attachments,
            "current_user"=> $currentuser,
            "qustion_users"=>$qustionUsers,
            "post_users"=>$postUsers,
            "appendixs"=>$appendix));
    }

    //公开课报名，根据用户是否登陆和是否设置价格及支付方式进入不同的报名页面
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
            if ($activity['needApproval']=='需要' or $activity['needApproval']=='yes') {
                $member['approvalStatus']='checking';
            }

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
        //默认是未登陆用户报名
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

    //如果免费，显示公开课报名成功页面，如果收费，并且线上支付，进入支付页面，如果收费，并且线下支付，进入报名成功页面，提示支付告知页面
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

    //公开课取消报名
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

   //首页公开课列表块
    public function activityBlockGridAction($activitys, $mode = 'default')
    {
        $currentuser=$this->getCurrentUser();
        $userId=$currentuser['id'];


        $activitys =  $this->getActivityService()->extActivitys($activitys);

        $activitys= $this->getActivityService()->mixActivitys($activitys,$userId);

       
      
        return $this->render("TopxiaWebBundle:Activity:activitys-block-grid.html.twig", array(
            'activitys' => $activitys,      
            'mode' => $mode,
        ));
    }

    //谁还在看公开课列表块
    public function activityBlockSameAction($activitys)
    {
      
        return $this->render("TopxiaWebBundle:Activity:activitys-block-same.html.twig", array(
            'activitys' => $activitys, 
        ));
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

    //公开课详细页面讲师块
    public function expertersBlockAction($activity)
    {
    
        return $this->render('TopxiaWebBundle:Activity:experters-block.html.twig', array(
            'activity' => $activity,
        ));
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
                //$this->sendWeibo($fields['castweibo'],$fields);
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
                    'qustion'=>$newqustion,
                    'user'=>$currentuser,
                ));
        }
        
    }

    public function emailCheckAction(Request $request)
    {
        $email = $request->query->get('value');
        $result = $this->getUserService()->isEmailAvaliable($email);
        if ($result) {
            $response = array('success' => true, 'message' => '该Email地址可以使用');
        } else {
            $response = array('success' => false, 'message' => '该Email地址已注册，如果您是该用户，请登陆！');
        }
        return $this->createJsonResponse($response);
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
            ->add('title', 'text',array('required'=>true))
            ->add('actType', 'act_type',array('multiple'=>false,'expanded'=>false,'required'=>true))
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




