<?php

namespace Classroom\ClassroomBundle\Controller;
use Topxia\Common\FileToolkit;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\WebBundle\Form\ClassroomReviewType;

class ClassroomController extends BaseController 
{   
    public function  exploreAction(Request $request)
    {
        $code = $request->query->get('code', '');

        $conditions = array(
            'status' => 'published',
            'private' => 0
        );

        if (!empty($code)) {
            $category = $this->getCategoryService()->getCategoryByCode($code);
            $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($category['id']);
            $categoryIds = array_merge($childrenIds, array($category['id']));
            $conditions['categoryIds'] = $categoryIds;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->searchClassroomsCount($conditions),
            9
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('createdTime','desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $classroomIds = ArrayToolkit::column($classrooms,'id');

        $allClassrooms = ArrayToolkit::index($classrooms,'id');

        return $this->render("ClassroomBundle:Classroom:explore.html.twig", array(
            'paginator' => $paginator,
            'classrooms' => $classrooms,
            'allClassrooms' => $allClassrooms,
            'path' => 'classroom_explore',
            'code' => $code
            ));
    }

    public function myClassroomAction()
    {   
        $user = $this->getCurrentUser();
        $progresses = array();
        $classrooms=array();

        $studentClassrooms=$this->getClassroomService()->searchMembers(array('role'=>'student','userId'=>$user->id),array('createdTime','desc'),0,9999);
        $auditorClassrooms=$this->getClassroomService()->searchMembers(array('role'=>'auditor','userId'=>$user->id),array('createdTime','desc'),0,9999);

        $classrooms=array_merge($studentClassrooms,$auditorClassrooms);

        $classroomIds=ArrayToolkit::column($classrooms,'classroomId');

        $classrooms=$this->getClassroomService()->findClassroomsByIds($classroomIds);

        foreach ($classrooms as $key => $classroom) {
            
            $courses=$this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
            $coursesCount=count($courses);

            $classrooms[$key]['coursesCount']=$coursesCount;
            
            $classroomId= array($classroom['id']);
            $member=$this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomId);
            $time=time()-$member[$classroom['id']]['createdTime'];
            $day=intval($time/(3600*24));

            $classrooms[$key]['day']=$day;

            $progresses[$classroom['id']] = $this->calculateUserLearnProgress($classroom, $user->id);
        }

        $members=$this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomIds);

        return $this->render("ClassroomBundle:Classroom:my-classroom.html.twig",array(
            'classrooms'=>$classrooms,
            'members'=>$members,
            'progresses'=>$progresses,
        )); 
    }

    public function headerAction($previewAs="", $classroomId)
    {
        $classroom=$this->getClassroomService()->getClassroom($classroomId);

        $user = $this->getCurrentUser();

        $courses=$this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        $coursesNum = count($courses);

        $checkMemberLevelResult = $classroomMemberLevel = null;
        if ($this->setting('vip.enabled')) {
            $classroomMemberLevel = $classroom['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($classroom['vipLevelId']) : null;
            if ($classroomMemberLevel) {
                $checkMemberLevelResult = $this->getVipService()->checkUserInMemberLevel($user['id'], $classroomMemberLevel['id']);
            }
        }

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if ($previewAs) {

            if (!$this->getClassroomService()->canManageClassroom($classroomId)) {

                $previewAs="";
            }
        }
        
        $member = $this->previewAsMember($previewAs, $member, $classroom);

        $lessonNum=0;
        $coinPrice=0;
        $price=0;
       
        foreach ($courses as $key => $course) {

            $lessonNum+=$course['lessonNum'];

            $coinPrice+=$course['coinPrice'];
            $price+=$course['price'];
        }

        $canFreeJoin = $this->canFreeJoin($courses, $user);
        
        if($member && $member["locked"] == "0"){

            return $this->render("ClassroomBundle:Classroom:classroom-join-header.html.twig",array(
                'classroom'=>$classroom,
                'courses'=>$courses,
                'lessonNum'=>$lessonNum,
                'coinPrice'=>$coinPrice,
                'price'=>$price,
                'member'=>$member,
                'checkMemberLevelResult'=>$checkMemberLevelResult,
                'classroomMemberLevel'=>$classroomMemberLevel,
                'coursesNum'=>$coursesNum,
                'canFreeJoin'=>$canFreeJoin
            ));

        }

        return $this->render("ClassroomBundle:Classroom:classroom-header.html.twig",array(
            'classroom'=>$classroom,
            'courses'=>$courses,
            'checkMemberLevelResult'=>$checkMemberLevelResult,
            'classroomMemberLevel'=>$classroomMemberLevel,
            'coursesNum'=>$coursesNum,
            'member'=>$member,
            'canFreeJoin'=>$canFreeJoin
        ));
    }

    /**
     * 如果用户已购买了此班级，或者用户是该班级的教师，则显示班级的Dashboard界面。
     * 如果用户未购买该班级，那么显示课程的营销界面。
     */
    public function showAction(Request $request, $id)
    {
        $classroom=$this->getClassroomService()->getClassroom($id);
        $previewAs="";

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        $currentUser = $this->getUserService()->getCurrentUser();

        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if ($request->query->get('previewAs')) {

            if ($this->getClassroomService()->canManageClassroom($id)) {

                $previewAs=$request->query->get('previewAs');
            }
        }

        $member = $this->previewAsMember($previewAs, $member, $classroom);
        if ($member && $member["locked"] == "0"){
            if ($member['role'] == 'student') {
                return $this->redirect($this->generateUrl('classroom_courses', array(
                    'classroomId'=>$id,
                )));
            }else{
                return $this->redirect($this->generateUrl('classroom_threads', array(
                    'classroomId'=>$id,
                )));
            }
        }

        return $this->redirect($this->generateUrl('classroom_introductions', array(
            'id'=>$id,
        )));

    }

    private function previewAsMember($previewAs="", $member, $classroom)
    {   
        $user = $this->getCurrentUser();

        if (in_array($previewAs, array('guest','auditor','member'))) {

            if ($previewAs == 'guest') {

                return null;
            }

            $member = array(
                'id'=>0,
                'classroomId'=>$classroom['id'],
                'userId'=>$user['id'],
                'orderId'=>0,
                'levelId'=>0,
                'noteNum'=>0,
                'threadNum'=>0,
                'remark'=>'',
                'role'=>'auditor',
                'locked'=>0,
                'createdTime'=>0
            );

            if ($previewAs == 'member') {

                $member['role'] = 'member';

            }
        }

        return $member;
    }

   public function introductionAction(Request $request, $id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);
        $introduction = $classroom['about'];
        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        return $this->render("ClassroomBundle:Classroom:introduction.html.twig", array(
            'introduction' => $introduction,
            'classroom' => $classroom,
            'member' => $member,
        ));

    }

    public function teachersBlockAction($classroom)
    {   
        if (empty($classroom['teacherIds'])) {
            $classroomTeacherIds=array();
        }else{
            $classroomTeacherIds=$classroom['teacherIds'];
        }

        $users = $this->getUserService()->findUsersByIds($classroomTeacherIds);
        $headTeacher = $this->getUserService()->getUser($classroom['headTeacherId']);
        $headTeacherprofiles = $this->getUserService()->getUserProfile($classroom['headTeacherId']);
        $profiles = $this->getUserService()->findUserProfilesByIds($classroomTeacherIds);
        $currentUser = $this->getCurrentUser();
        $isFollowed = $this->getUserService()->isFollowed($currentUser['id'], $headTeacher['id']);

        if($headTeacher && !(in_array($headTeacher, $users))){
            $teachersCount = 1 + count($users);
        }else{
            $teachersCount = count($users);
        }

        return $this->render('ClassroomBundle:Classroom:teachers-block.html.twig', array(
            'classroom' => $classroom,
            'users' => $users,
            'profiles' => $profiles,
            'headTeacher' => $headTeacher,
            'headTeacherprofiles' => $headTeacherprofiles,
            'teachersCount'=>$teachersCount,
            'isFollowed' => $isFollowed,
        ));
    }

    public function roleAction($previewAs="",$classroomId)
    {   
        $classroom=$this->getClassroomService()->getClassroom($classroomId);

        $user = $this->getCurrentUser();

        $courses=$this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        
        $checkMemberLevelResult = $classroomMemberLevel = null;
        if ($this->setting('vip.enabled')) {
            $classroomMemberLevel = $classroom['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($classroom['vipLevelId']) : null;
            if ($classroomMemberLevel) {
                $checkMemberLevelResult = $this->getVipService()->checkUserInMemberLevel($user['id'], $classroomMemberLevel['id']);
            }
        }

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if ($previewAs) {

            if (!$this->getClassroomService()->canManageClassroom($classroomId)) {

                $previewAs="";
            }
        }
        
        $member = $this->previewAsMember($previewAs, $member, $classroom);

        $coinPrice=0;
        $price=0;
       
        foreach ($courses as $key => $course) {

            $coinPrice+=$course['coinPrice'];
            $price+=$course['price'];
        }

        if($member && $member["locked"] == "0"){
            return $this->render("ClassroomBundle:Classroom:role.html.twig",array(
                'classroom'=>$classroom,
                'courses'=>$courses,
                'coinPrice'=>$coinPrice,
                'price'=>$price,
                'member'=>$member,
                'checkMemberLevelResult'=>$checkMemberLevelResult,
                'classroomMemberLevel'=>$classroomMemberLevel,
            ));
        }
        return new Response();
    }

    public function latestMembersBlockAction($classroom, $count = 10)
    {
        $students = $this->getClassroomService()->findClassroomStudents($classroom['id'], 0, 12);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($students, 'userId'));
        return $this->render('ClassroomBundle:Classroom:latest-members-block.html.twig', array(
            'students' => $students,
            'users' => $users,
        ));
    }

    public function classroomStatusBlockAction($classroom, $count = 10)
    {
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        $courseIds = ArrayToolkit::column($courses, 'id');
        $conditions = array(
            'private' => 0,
            'courseIds' => $courseIds
        );
        $learns = $this->getStatusService()->searchStatuses(
            $conditions,
            array('createdTime', 'DESC'),
            0,
            $count
        );

        $ownerIds = ArrayToolkit::column($learns, 'userId');

        $owners = $this->getUserService()->findUsersByIds($ownerIds);

        foreach ($learns as $key => $learn) {
            $learns[$key]['user'] = $owners[$learn['userId']];
        }
        return $this->render('TopxiaWebBundle:Status:status-block.html.twig', array(
            'learns' => $learns,
        ));
    }

    public function signPageAction($classroomId)
    {
        $user=$this->getCurrentUser();
        
        $classroom=$this->getClassroomService()->getClassroom($classroomId);

        $isSignedToday = $this->getSignService()->isSignedToday($user->id, 'classroom_sign', $classroom['id']);

        $week=array('日','一','二','三','四','五','六');

        $userSignStatistics = $this->getSignService()->getSignUserStatistics($user->id, 'classroom_sign', $classroom['id']);

        $day=date('d',time());
  
        $signDay=$this->getSignService()->getSignRecordsByPeriod($user->id, 'classroom_sign', $classroom['id'], date('Y-m',time()), date('Y-m-d',time()+3600));
        $notSign=$day-count($signDay);

        return $this->render("ClassroomBundle:Classroom:sign.html.twig",array(
            'classroom'=>$classroom,
            'isSignedToday'=>$isSignedToday,
            'userSignStatistics'=>$userSignStatistics,
            'notSign'=>$notSign,
            'week'=>$week[date('w',time())]));
    }

    public function signAction(Request $request, $classroomId)
    {   
        $user = $this->getCurrentUser();
        $userSignStatistics = array();

        $this->checkClassroomStatus($classroomId);

        $member = $this->getClassroomService()->getClassroomMember($classroomId,$user['id']);

        if($this->getClassroomService()->canTakeClassroom($classroomId) || (isset($member) && $member['role']=="auditor") ){

            $this->getSignService()->userSign($user['id'], 'classroom_sign', $classroomId);

            $userSignStatistics = $this->getSignService()->getSignUserStatistics($user->id, 'classroom_sign', $classroomId);
        }
        
        return $this->createJsonResponse($userSignStatistics);
    }

    public function getSignedRecordsByPeriodAction(Request $request, $classroomId)
    {   
        $user=$this->getCurrentUser();
        $userId=$user['id'];

        $startDay = $request->query->get('startDay');
        $endDay = $request->query->get('endDay');
    
        $userSigns = $this->getSignService()->getSignRecordsByPeriod($userId, 'classroom_sign', $classroomId, $startDay, $endDay);
        $result = array();
        $result['records'] = array();
        if($userSigns) {
            foreach ($userSigns as $userSign) {
            $result['records'][] = array(
                'day' => date('d',$userSign['createdTime']),
                'time' => date('G点m分',$userSign['createdTime']),
                'rank' => $userSign['rank']);
            }
        }
        $userSignStatistics = $this->getSignService()->getSignUserStatistics($userId, 'classroom_sign', $classroomId);
        $classSignStatistics = $this->getSignService()->getSignTargetStatistics('classroom_sign', $classroomId, date('Ymd', time()));

        $result['todayRank'] = $this->getSignService()->getTodayRank($userId, 'classroom_sign', $classroomId);
        $result['signedNum'] = $classSignStatistics['signedNum'];
        $result['keepDays'] = $userSignStatistics['keepDays'];
        
        return $this->createJsonResponse($result);
    }

    public function becomeStudentAction(Request $request, $id)
    {
        if (!$this->setting('vip.enabled')) {
            $this->createAccessDeniedException();
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createAccessDeniedException();
        }
        $this->getClassroomService()->becomeStudent($id, $user['id'], array('becomeUseMember' => true));
        return $this->redirect($this->generateUrl('classroom_show', array('id' => $id)));
    }

    public function exitAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        $member = $this->getClassroomService()->getClassroomMember($id, $user["id"]);

        if (empty($member)) {
            throw $this->createAccessDeniedException('您不是班级的学员。');
        }

        if (!in_array($member["role"], array("auditor", "student"))) {
            throw $this->createAccessDeniedException('您不是班级的学员。');
        }

        if (!empty($member['orderId'])) {
            throw $this->createAccessDeniedException('有关联的订单，不能直接退出学习。');
        }

        $this->getClassroomService()->exitClassroom($id, $user["id"]);

        return $this->redirect($this->generateUrl('classroom_show', array('id' => $id)));
    }

    public function becomeAuditorAction(Request $request, $id)
    {
        $user=$this->getCurrentUser();
        if(!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $classroom=$this->getClassroomService()->getClassroom($id);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        if($this->getClassroomService()->canTakeClassroom($id)){

            $member = $this->getClassroomService()->getClassroomMember($id, $user['id']);
            if($member){

                goto response;
            }  
            
        }

        if($this->getClassroomService()->isClassroomAuditor($id, $user["id"])) {

            goto response;
         
        }

        $this->getClassroomService()->becomeAuditor($id, $user["id"]);

        response:
        return $this->redirect($this->generateUrl('classroom_show', array('id' => $id)));
    }

    public function canviewAction(Request $request, $classroomId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $result = $this->getClassroomService()->canLookClassroom($classroomId);

        return $this->createJsonResponse($result);

    }

    public function classroomBlockAction($courseId)
    {
        $classrooms = $this->getClassroomService()->findClassroomsByCourseId($courseId);
        $classroomIds = ArrayToolkit::column($classrooms,'classroomId');

        $classroom = empty($classroomIds) || count($classroomIds)==0 ? null : $this->getClassroomService()->getClassroom($classroomIds[0]);

        return $this->render("ClassroomBundle:Classroom:classroom-block.html.twig",array(
            'classroom'=>$classroom
        ));

    }

    private function canFreeJoin($courses, $user)
    {   
        $classroomSetting = $this->getSettingService()->get('classroom');
        if(!$classroomSetting['discount_buy']){
            return false;
        }

        $courseIds = ArrayToolkit::column($courses, "id");
        $courseMembers = $this->getCourseService()->findCoursesByStudentIdAndCourseIds($user["id"], $courseIds);
        $isJoinedCourseIds = ArrayToolkit::column($courseMembers, "courseId");
        $coinSetting = $this->getSettingService()->get("coin");
        $coinEnable = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"]==1;
        $priceType = "RMB";
        if($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
            $priceType = $coinSetting["price_type"];
        }

        $classroomSetting = $this->getSettingService()->get("classroom");

        foreach ($courses as $course) {

            if(
                !in_array($course["id"],$isJoinedCourseIds)
                && (($priceType == "RMB" && $course["price"]>0) 
                || ($priceType == "Coin" && $course["coinPrice"]>0))){
                return false;
            }

        }
        return true;
    }

    private function checkClassroomStatus($classroomId)
    {
        $classroom=$this->getClassroomService()->getClassroom($classroomId);

        if(!$classroom){

            throw $this->createNotFoundException();
        }

        if($classroom['status'] != "published" ){

            throw $this->createNotFoundException();
        }

    }

    private function calculateUserLearnProgress($classroom, $userId)
    {
        $courses=$this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        $courseIds = ArrayToolkit::column($courses,'id');
        $findLearnedCourses = array();
        foreach ($courseIds as $key => $value) {
            $LearnedCourses=$this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($value,$userId);
            if (!empty($LearnedCourses)) {
                $findLearnedCourses[] = $LearnedCourses;
            }
        }

        $learnedCoursesCount = count($findLearnedCourses);
        $coursesCount=count($courses);

        if ($coursesCount == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($learnedCoursesCount / $coursesCount * 100) . '%';

        return array (
            'percent' => $percent,
            'number' => $learnedCoursesCount,
            'total' => $coursesCount
        );
    }

    public function classroomThreadsAction(Request $request, $type)
    {
        $user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $classrooms=array();
        $teacherClassrooms=$this->getClassroomService()->searchMembers(array('role'=>'teacher','userId'=>$user->id),array('createdTime','desc'),0,9999);
        $headTeacherClassrooms=$this->getClassroomService()->searchMembers(array('role'=>'headTeacher','userId'=>$user->id),array('createdTime','desc'),0,9999);

        $classrooms=array_merge($teacherClassrooms,$headTeacherClassrooms);

        $classroomIds=ArrayToolkit::column($classrooms,'classroomId');

        $classrooms=$this->getClassroomService()->findClassroomsByIds($classroomIds);

        if (empty($classrooms)) {
            return $this->render('ClassroomBundle:Classroom:my-teaching-threads.html.twig', array(
                'type'=>$type,
                'threadType' => 'classroom',
                'threads' => array()
            ));
        }

        $conditions = array(
            'targetIds' => $classroomIds,
            'targetType' => 'classroom',
            'type' => $type
            );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'lastPostUserId'));

        return $this->render('ClassroomBundle:Classroom:my-teaching-threads.html.twig', array(
            'paginator' => $paginator,
            'threads' => $threads,
            'users'=> $users,
            'classrooms' => $classrooms,
            'type'=>$type,
            'threadType' => 'classroom',
        ));
    }

    public function classroomDiscussionsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'userId'=>$user['id'],
            'type'=>'discussion',
            'targetType' => 'classroom',
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'lastPostUserId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($threads, 'targetId'));

        return $this->render('ClassroomBundle:Classroom:classroom-discussions.html.twig',array(
            'threadType' => 'classroom',
            'paginator' => $paginator,
            'threads' => $threads,
            'users'=> $users,
            'classrooms' => $classrooms,
            ));
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    private function getClassroomService() 
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getSignService()
    {
        return $this->getServiceKernel()->createService('Sign.SignService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
  
    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getClassroomOrderService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomOrderService');    
    }

    protected function getClassroomReviewService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomReviewService');
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}
