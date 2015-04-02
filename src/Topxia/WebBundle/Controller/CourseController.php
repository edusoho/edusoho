<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Topxia\Common\Paginator;
use Topxia\WebBundle\Form\CourseType;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\LiveClientFactory;

class CourseController extends BaseController
{
	public function exploreAction(Request $request, $category)
	{
		if (!empty($category)) {
			if (ctype_digit((string) $category)) {
				$category = $this->getCategoryService()->getCategory($category);
			} else {
				$category = $this->getCategoryService()->getCategoryByCode($category);
			}

			if (empty($category)) {
				throw $this->createNotFoundException();
			}
		} else {
			$category = array('id' => null);
		}
		

		$sort = $request->query->get('sort', 'latest');
		$conditions = array(
			'status' => 'published',
			'type' => 'normal',
			'categoryId' => $category['id'],
			'recommended' => ($sort == 'recommendedSeq') ? 1 : null,
			'price' => ($sort == 'free') ? '0.00' : null
		);

		$paginator = new Paginator(
			$this->get('request'),
			$this->getCourseService()->searchCourseCount($conditions)
			, 10
		);

		$courses = $this->getCourseService()->searchCourses(
			$conditions, $sort,
			$paginator->getOffsetCount(),
			$paginator->getPerPageCount()
		);

		$group = $this->getCategoryService()->getGroupByCode('course');
		if (empty($group)) {
			$categories = array();
		} else {
			$categories = $this->getCategoryService()->getCategoryTree($group['id']);
		}
		
		return $this->render('TopxiaWebBundle:Course:explore.html.twig', array(
			'courses' => $courses,
			'category' => $category,
			'sort' => $sort,
			'paginator' => $paginator,
			'categories' => $categories,
			'consultDisplay' => true,
			

		));
	}

	public function archiveAction(Request $request)
	{   
		$conditions = array(
			'status' => 'published'
		);

		$paginator = new Paginator(
			$this->get('request'),
			$this->getCourseService()->searchCourseCount($conditions)
			, 30
		);

		$courses = $this->getCourseService()->searchCourses(
			$conditions, 'latest',
			$paginator->getOffsetCount(),
			$paginator->getPerPageCount()
		);

		$userIds = array();
		foreach ($courses as &$course) {
			$course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
			$userIds = array_merge($userIds, $course['teacherIds']);
		}

		$users = $this->getUserService()->findUsersByIds($userIds);

		return $this->render('TopxiaWebBundle:Course:archive.html.twig',array(
			'courses' => $courses,
			'paginator' => $paginator,
			'users' => $users
		));
	}

	public function archiveCourseAction(Request $request, $id)
	{
		$course = $this->getCourseService()->getCourse($id);
		$lessons = $this->getCourseService()->searchLessons(array('courseId' => $course['id'],'status' => 'published'), array('createdTime', 'ASC'), 0, 1000);
		$tags = $this->getTagService()->findTagsByIds($course['tags']);
		$category = $this->getCategoryService()->getCategory($course['categoryId']);

		return $this->render('TopxiaWebBundle:Course:archiveCourse.html.twig', array(
			'course' => $course,
			'lessons' => $lessons,
			'tags' => $tags,
			'category' => $category
		));
	}

	public function archiveLessonAction(Request $request, $id, $lessonId)
	{   

		$course = $this->getCourseService()->getCourse($id);

		$lessons = $this->getCourseService()->searchLessons(array('courseId' => $course['id'],'status' => 'published'), array('createdTime', 'ASC'), 0, 1000);

		$tags = $this->getTagService()->findTagsByIds($course['tags']);

		if ($lessonId == '' && $lessons != null ) {
			$currentLesson = $lessons[0];
		} else {
			$currentLesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
		}

		return $this->render('TopxiaWebBundle:Course:archiveLesson.html.twig',array(
			'course' => $course,
			'lessons' => $lessons,
			'currentLesson' => $currentLesson,
			'tags' => $tags
		));
	}

	public function infoAction(Request $request, $id)
	{
		$course = $this->getCourseService()->getCourse($id);
		$category = $this->getCategoryService()->getCategory($course['categoryId']);
		$tags = $this->getTagService()->findTagsByIds($course['tags']);
		return $this->render('TopxiaWebBundle:Course:info.html.twig', array(
			'course' => $course,
			'category' => $category,
			'tags' => $tags,
		));
	}

	public function teacherInfoAction(Request $request, $courseId, $id)
	{
		$currentUser = $this->getCurrentUser();

		$course = $this->getCourseService()->getCourse($courseId);
		$user = $this->getUserService()->getUser($id);
		$profile = $this->getUserService()->getUserProfile($id);

		$isFollowing = $this->getUserService()->isFollowed($currentUser->id, $user['id']);

		return $this->render('TopxiaWebBundle:Course:teacher-info-modal.html.twig', array(
			'user' => $user,
			'profile' => $profile,
			'isFollowing' => $isFollowing,
		));
	}

	public function membersAction(Request $request, $id)
	{
		list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

		$paginator = new Paginator(
			$request,
			$this->getCourseService()->getCourseStudentCount($course['id']),
			6
		);

		$students = $this->getCourseService()->findCourseStudents(
			$course['id'],
			$paginator->getOffsetCount(),
			$paginator->getPerPageCount()
		);
		$studentUserIds = ArrayToolkit::column($students, 'userId');
		$users = $this->getUserService()->findUsersByIds($studentUserIds);
		$followingIds = $this->getUserService()->filterFollowingIds($this->getCurrentUser()->id, $studentUserIds);

		$progresses = array();
		foreach ($students as $student) {
			$progresses[$student['userId']] = $this->calculateUserLearnProgress($course, $student);
		}

		return $this->render('TopxiaWebBundle:Course:members-modal.html.twig', array(
			'course' => $course,
			'students' => $students,
			'users'=>$users,
			'progresses' => $progresses,
			'followingIds' => $followingIds,
			'paginator' => $paginator,
			'canManage' => $this->getCourseService()->canManageCourse($course['id']),
		));
	}

	/**
	 * 如果用户已购买了此课程，或者用户是该课程的教师，则显示课程的Dashboard界面。
	 * 如果用户未购买该课程，那么显示课程的营销界面。
	 */
	public function showAction(Request $request, $id)
	{
		$course = $this->getCourseService()->getCourse($id);
        $code = 'ChargeCoin';
        $ChargeCoin = $this->getAppService()->findInstallApp($code);
        
        $courseSetting=$this->getSettingService()->get('course',array());
        
        if (isset($courseSetting['coursesPrice'])) {
                $coursesPrice=$courseSetting['coursesPrice'];
        }else{
                $coursesPrice=0;
        }

        $defaultSetting = $this->getSettingService()->get('default', array());

        if (isset($defaultSetting['courseShareContent'])){
            $courseShareContent = $defaultSetting['courseShareContent'];
        } else {
        	$courseShareContent = "";
        }

        $valuesToBeReplace = array('{{course}}');
        $valuesToReplace = array($course['title']);
        $courseShareContent = str_replace($valuesToBeReplace, $valuesToReplace, $courseShareContent);



		$nextLiveLesson = null;

		$weeks = array("日","一","二","三","四","五","六");

		$currentTime = time();
 
		if (empty($course)) {
			throw $this->createNotFoundException();
		}

		if ($course['type'] == 'live') {
			$conditions = array(
				'courseId' => $course['id'],
				'startTimeGreaterThan' => time(),
				'status' => 'published'
			);
			$nextLiveLesson = $this->getCourseService()->searchLessons( $conditions, array('startTime', 'ASC'), 0, 1);
			if ($nextLiveLesson) {
				$nextLiveLesson = $nextLiveLesson[0];
			}
		};

		$previewAs = $request->query->get('previewAs');

		$user = $this->getCurrentUser();

		$items = $this->getCourseService()->getCourseItems($course['id']);
		$mediaMap = array();
		foreach ($items as $item) {
			if (empty($item['mediaId'])) {
				continue;
			}

			if (empty($mediaMap[$item['mediaId']])) {
				$mediaMap[$item['mediaId']] = array();
			}
			$mediaMap[$item['mediaId']][] = $item['id'];
		}

		$mediaIds = array_keys($mediaMap);
		$files = $this->getUploadFileService()->findFilesByIds($mediaIds);

		$member = $user ? $this->getCourseService()->getCourseMember($course['id'], $user['id']) : null;

		$this->getCourseService()->hitCourse($id);

		$member = $this->previewAsMember($previewAs, $member, $course);

		$homeworkLessonIds =array();
		$exercisesLessonIds =array();
		if($this->isPluginInstalled("Homework")) {
            $lessons = $this->getCourseService()->getCourseLessons($course['id']);
            $lessonIds = ArrayToolkit::column($lessons, 'id');
            $homeworks = $this->getHomeworkService()->findHomeworksByCourseIdAndLessonIds($course['id'], $lessonIds);
            $exercises = $this->getExerciseService()->findExercisesByLessonIds($lessonIds);
            $homeworkLessonIds = ArrayToolkit::column($homeworks,'lessonId');
            $exercisesLessonIds = ArrayToolkit::column($exercises,'lessonId');
		}

		if($this->isPluginInstalled("Classroom") && empty($member)) {
			$classroomMembers = $this->getClassroomMembersByCourseId($id);
			foreach ($classroomMembers as $classroomMember) {
				if(in_array($classroomMember["role"], array("student")) && !$this->getCourseService()->isCourseStudent($id, $user["id"])) {
					$member = $this->getCourseService()->becomeStudentByClassroomJoined($id, $user["id"], $classroomMember["classroomId"]);
				}
			}
		}

		$classrooms=array();
		$isLearnInClassrooms=array();
		if ($this->isPluginInstalled("Classroom")) {
			$classroomIds=ArrayToolkit::column($this->getClassroomService()->findClassroomsByCourseId($id),'classroomId');
			foreach ($classroomIds as $key => $value) {
				$classrooms[$value]=$this->getClassroomService()->getClassroom($value);

				if ($this->getClassroomService()->isClassroomStudent($value, $user->id) or $this->getClassroomService()->isClassroomTeacher($value, $user->id)) {

					$isLearnInClassrooms[] = $classrooms[$value];

				}
			}
		}

		if ($member && empty($member['locked'])) {
			$learnStatuses = $this->getCourseService()->getUserLearnLessonStatuses($user['id'], $course['id']);
			//判断用户deadline到了，但是还是限免课程，将用户deadline延长
			if( $member['deadline'] < time() && !empty($course['freeStartTime']) && !empty($course['freeEndTime']) && $course['freeEndTime'] >= time()) {
				$member = $this->getCourseService()->updateCourseMember($member['id'], array('deadline'=>$course['freeEndTime']));
			}
			if($coursesPrice ==1){
				$course['price'] =0;
				$course['coinPrice'] =0;
			}

			return $this->render("TopxiaWebBundle:Course:dashboard.html.twig", array(
				'course' => $course,
				'type' => $course['type'],
				'member' => $member,
				'items' => $items,
				'learnStatuses' => $learnStatuses,
				'currentTime' => $currentTime,
				'weeks' => $weeks,
				'files' => ArrayToolkit::index($files,'id'),
				'ChargeCoin'=> $ChargeCoin,
				'homeworkLessonIds' => $homeworkLessonIds,
				'exercisesLessonIds' => $exercisesLessonIds,
				'isLearnInClassrooms'=> $isLearnInClassrooms
			));
		}
		
		$groupedItems = $this->groupCourseItems($items);
		$hasFavorited = $this->getCourseService()->hasFavoritedCourse($course['id']);

		$category = $this->getCategoryService()->getCategory($course['categoryId']);
		$tags = $this->getTagService()->findTagsByIds($course['tags']);

		$checkMemberLevelResult = $courseMemberLevel = null;
		if ($this->setting('vip.enabled')) {
			$courseMemberLevel = $course['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($course['vipLevelId']) : null;
			if ($courseMemberLevel) {
				$checkMemberLevelResult = $this->getVipService()->checkUserInMemberLevel($user['id'], $courseMemberLevel['id']);
			}
		}

		$courseReviews = $this->getReviewService()->findCourseReviews($course['id'],'0','1');

		$freeLesson=$this->getCourseService()->searchLessons(array('courseId'=>$id,'type'=>'video','status'=>'published','free'=>'1'),array('createdTime','ASC'),0,1);
		if($freeLesson)$freeLesson=$freeLesson[0];
		
		if($coursesPrice == 1){
			$course['price'] =0;
			$course['coinPrice'] =0;
		}

		return $this->render("TopxiaWebBundle:Course:show.html.twig", array(
			'course' => $course,
			'member' => $member,
			'freeLesson'=>$freeLesson,
			'courseMemberLevel' => $courseMemberLevel,
			'checkMemberLevelResult' => $checkMemberLevelResult,
			'groupedItems' => $groupedItems,
			'hasFavorited' => $hasFavorited,
			'category' => $category,
			'previewAs' => $previewAs,
			'tags' => $tags,
			'nextLiveLesson' => $nextLiveLesson,
			'currentTime' => $currentTime,
			'courseReviews' => $courseReviews,
			'weeks' => $weeks,
			'courseShareContent'=>$courseShareContent,
			'consultDisplay' => true,
			'ChargeCoin'=> $ChargeCoin,
			'classrooms'=> $classrooms
		));

	}

	private function canShowCourse($course, $user)
	{
		return ($course['status'] == 'published') or 
			$user->isAdmin() or 
			$this->getCourseService()->isCourseTeacher($course['id'],$user['id']) ;
	}

	private function previewAsMember($as, $member, $course)
	{
		$user = $this->getCurrentUser();
		if (empty($user->id)) {
			return null;
		}


		if (in_array($as, array('member', 'guest'))) {
			if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
				$member = array(
					'id' => 0,
					'courseId' => $course['id'],
					'userId' => $user['id'],
					'levelId' => 0,
					'learnedNum' => 0,
					'isLearned' => 0,
					'seq' => 0,
					'isVisible' => 0,
					'role' => 'teacher',
					'locked' => 0,
					'createdTime' => time(),
					'deadline' => 0
				);
			}

			if (empty($member) or $member['role'] != 'teacher') {
				return $member;
			}

			if ($as == 'member') {
				$member['role'] = 'student';
			} else {
				$member = null;
			}
		}

		return $member;
	}

	private function groupCourseItems($items)
	{
		$grouped = array();

		$list = array();
		foreach ($items as $id => $item) {
			if ($item['itemType'] == 'chapter') {
				if (!empty($list)) {
					$grouped[] = array('type' => 'list', 'data' => $list);
					$list = array();
				}
				$grouped[] = array('type' => 'chapter', 'data' => $item);
			} else {
				$list[] = $item;
			}
		}

		if (!empty($list)) {
			$grouped[] = array('type' => 'list', 'data' => $list);
		}

		return $grouped;
	}

	private function calculateUserLearnProgress($course, $member)
	{
		if ($course['lessonNum'] == 0) {
			return array('percent' => '0%', 'number' => 0, 'total' => 0);
		}

		$percent = intval($member['learnedNum'] / $course['lessonNum'] * 100) . '%';

		return array (
			'percent' => $percent,
			'number' => $member['learnedNum'],
			'total' => $course['lessonNum']
		);
	}
	
	public function favoriteAction(Request $request, $id)
	{
		$this->getCourseService()->favoriteCourse($id);
		return $this->createJsonResponse(true);
	}

	public function unfavoriteAction(Request $request, $id)
	{
		$this->getCourseService()->unfavoriteCourse($id);
		return $this->createJsonResponse(true);
	}

	public function createAction(Request $request)
	{  
		$user = $this->getUserService()->getCurrentUser();
		$userProfile = $this->getUserService()->getUserProfile($user['id']);

		$isLive = $request->query->get('flag');
		$type = ($isLive == "isLive") ? 'live' : 'normal';

		if ($type == 'live') {

			$courseSetting = $this->setting('course', array());

			if (!empty($courseSetting['live_course_enabled'])) {
				$client = LiveClientFactory::createClient();
				$capacity = $client->getCapacity();
			} else {
				$capacity = array();
			}

			if (empty($courseSetting['live_course_enabled'])) {
				return $this->createMessageResponse('info', '请前往后台开启直播,尝试创建！');
			}

			if (empty($capacity['capacity']) && !empty($courseSetting['live_course_enabled'])) {
				return $this->createMessageResponse('info', '请联系EduSoho官方购买直播教室，然后才能开启直播功能！');
			}
		}

		if (false === $this->get('security.context')->isGranted('ROLE_TEACHER')) {
			throw $this->createAccessDeniedException();
		}

		if ($request->getMethod() == 'POST') {
			$course = $request->request->all();
			$course = $this->getCourseService()->createCourse($course);
			return $this->redirect($this->generateUrl('course_manage', array('id' => $course['id'])));
		}

		return $this->render('TopxiaWebBundle:Course:create.html.twig', array(
			'userProfile'=>$userProfile,
			'type'=>$type
		));
	}

	public function exitAction(Request $request, $id)
	{
		list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
		$user = $this->getCurrentUser();

		if (empty($member)) {
			throw $this->createAccessDeniedException('您不是课程的学员。');
		}

		if ($member["joinedType"] == "course" && !empty($member['orderId'])) {
			throw $this->createAccessDeniedException('有关联的订单，不能直接退出学习。');
		}

		$this->getCourseService()->removeStudent($course['id'], $user['id']);
		return $this->createJsonResponse(true);
	}

	public function becomeUseMemberAction(Request $request, $id)
	{
		if (!$this->setting('vip.enabled')) {
			$this->createAccessDeniedException();
		}

		$user = $this->getCurrentUser();
		if (!$user->isLogin()) {
			$this->createAccessDeniedException();
		}
		$this->getCourseService()->becomeStudent($id, $user['id'], array('becomeUseMember' => true));
		return $this->createJsonResponse(true);
	}

	public function learnAction(Request $request, $id)
	{
		$user = $this->getCurrentUser();

		if (!$user->isLogin()) {
			$request->getSession()->set('_target_path', $this->generateUrl('course_show', array('id' => $id)));
			return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
		}

		$course = $this->getCourseService()->getCourse($id);


		if (empty($course)) {
			throw $this->createNotFoundException("课程不存在，或已删除。");
		}

		if (!$this->getCourseService()->canTakeCourse($id)) {
			return $this->createMessageResponse('info', "您还不是课程《{$course['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
		}
		
		try{
			list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
			if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
				return $this->redirect($this->generateUrl('course_show',array('id' => $id)));
			}

			if ($member && $member['levelId'] > 0) {
				if ($this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
					return $this->redirect($this->generateUrl('course_show',array('id' => $id)));
				}
			}



		}catch(Exception $e){
			throw $this->createAccessDeniedException('抱歉，未发布课程不能学习！');
		}
		return $this->render('TopxiaWebBundle:Course:learn.html.twig', array(
			'course' => $course,
		));
	}

	public function recordLearningTimeAction(Request $request,$lessonId,$time)
	{	
		$user = $this->getCurrentUser();

		$this->getCourseService()->waveLearningTime($lessonId,$user['id'],$time*60);

		return $this->createJsonResponse(true);
	}


    public function detailDataAction($id)
    {   
        $course = $this->getCourseService()->tryManageCourse($id);

        $count = $this->getCourseService()->getCourseStudentCount($id);
        $paginator = new Paginator($this->get('request'), $count, 20);

        $students = $this->getCourseService()->findCourseStudents($id, $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        foreach ($students as $key => $student) {
            
            $user=$this->getUserService()->getUser($student['userId']);
            $students[$key]['nickname']=$user['nickname'];

            $questionCount=$this->getThreadService()->searchThreadCount(array('courseId'=>$id,'type'=>'question','userId'=>$user['id']));
            $students[$key]['questionCount']=$questionCount;

            if( $student['learnedNum']>=$course['lessonNum'] && $course['lessonNum']>0){
                $finishLearn=$this->getCourseService()->searchLearns(array('courseId'=>$id,'userId'=>$user['id'],'sttaus'=>'finished'),array('finishedTime','DESC'),0,1);
                $students[$key]['fininshTime']=$finishLearn[0]['finishedTime'];

                $students[$key]['fininshDay']=intval(($finishLearn[0]['finishedTime']-$student['createdTime'])/(60*60*24));
            }else{
                $students[$key]['fininshDay']=intval((time()-$student['createdTime'])/(60*60*24));
            }

            $learnTime=$this->getCourseService()->searchLearnTime(array('userId'=>$user['id'],'courseId'=>$id));
            $students[$key]['learnTime']=$learnTime;
        }

        return $this->render('TopxiaWebBundle:Course:course-data-modal.html.twig', array(
            'course'=>$course,
            'paginator'=>$paginator,
            'students'=>$students,
            ));
    }

	public function recordWatchingTimeAction(Request $request,$lessonId,$time)
	{	
		$user = $this->getCurrentUser();

		$this->getCourseService()->waveWatchingTime($user['id'],$lessonId,$time*60);

		return $this->createJsonResponse(true);
	}

	public function watchPlayAction(Request $request,$lessonId)
	{	
		$user = $this->getCurrentUser();

		$this->getCourseService()->watchPlay($user['id'],$lessonId);

		return $this->createJsonResponse(true);
	}

	public function watchPausedAction(Request $request,$lessonId)
	{	
		$user = $this->getCurrentUser();

		$this->getCourseService()->watchPaused($user['id'],$lessonId);

		return $this->createJsonResponse(true);
	}

	public function addMemberExpiryDaysAction(Request $request, $courseId, $userId)
	{
		$user = $this->getUserService()->getUser($userId);
		$course = $this->getCourseService()->getCourse($courseId);

		if ($request->getMethod() == 'POST') {
			$fields = $request->request->all();

			$this->getCourseService()->addMemberExpiryDays($courseId, $userId, $fields['expiryDay']);
			return $this->createJsonResponse(true);
		}
		$default = $this->getSettingService()->get('default', array());
		return $this->render('TopxiaWebBundle:CourseStudentManage:set-expiryday-modal.html.twig', array(
			'course' => $course,
			'user' => $user,
			 'default'=> $default
		));
	}


	/**
	 * Block Actions
	 */

	public function headerAction($course, $manage = false)
	{
		$user = $this->getCurrentUser();

		$member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);

		$users = empty($course['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($course['teacherIds']);

        $defaultSetting = $this->getSettingService()->get('default', array());

        if (isset($defaultSetting['courseShareContent'])){
            $courseShareContent = $defaultSetting['courseShareContent'];
        } else {
        	$courseShareContent = "";
        }

        $valuesToBeReplace = array('{{course}}');
        $valuesToReplace = array($course['title']);
        $courseShareContent = str_replace($valuesToBeReplace, $valuesToReplace, $courseShareContent);

		if (empty($member)) {
			$member['deadline'] = 0; 
			$member['levelId'] = 0;
		}

		$isNonExpired = $this->getCourseService()->isMemberNonExpired($course, $member);

		if ($member['levelId'] > 0) {
			$vipChecked = $this->getVipService()->checkUserInMemberLevel($user['id'], $course['vipLevelId']);
		} else {
			$vipChecked = 'ok';
		}

		$classroomMembers = $this->getClassroomMembersByCourseId($course["id"]);
		$classroomMemberRoles = ArrayToolkit::column($classroomMembers, "role");
		if((isset($member["role"]) && isset($member["joinedType"]) && $member["role"] == 'student' && $member["joinedType"] == 'course') 
			|| (isset($member["joinedType"]) && $member["joinedType"] == 'classroom' && (empty($classroomMemberRoles) || count($classroomMemberRoles) == 0))) {
			$canExit = true;
		} else {
			$canExit = false;
		}

		return $this->render('TopxiaWebBundle:Course:header.html.twig', array(
			'course' => $course,
			'canManage' => $this->getCourseService()->canManageCourse($course['id']),
			'canExit' => $canExit,
			'member' => $member,
			'users' => $users,
			'manage' => $manage,
			'isNonExpired' => $isNonExpired,
			'vipChecked' => $vipChecked,
			'courseShareContent' => $courseShareContent,
			'isAdmin' => $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')
		));
	}

	public function teachersBlockAction($course)
	{
		$users = $this->getUserService()->findUsersByIds($course['teacherIds']);
		$profiles = $this->getUserService()->findUserProfilesByIds($course['teacherIds']);

		return $this->render('TopxiaWebBundle:Course:teachers-block.html.twig', array(
			'course' => $course,
			'users' => $users,
			'profiles' => $profiles,
		));
	}

	public function progressBlockAction($course)
	{
		$user = $this->getCurrentUser();

		$member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);
		$nextLearnLesson = $this->getCourseService()->getUserNextLearnLesson($user['id'], $course['id']);

		$progress = $this->calculateUserLearnProgress($course, $member);
		return $this->render('TopxiaWebBundle:Course:progress-block.html.twig', array(
			'course' => $course,
			'member' => $member,
			'nextLearnLesson' => $nextLearnLesson,
			'progress'  => $progress,
		));
	}

	public function latestMembersBlockAction($course, $count = 10)
	{
		$students = $this->getCourseService()->findCourseStudents($course['id'], 0, 12);
		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($students, 'userId'));
		return $this->render('TopxiaWebBundle:Course:latest-members-block.html.twig', array(
			'students' => $students,
			'users' => $users,
		));
	}

	public function coursesBlockAction($courses, $view = 'list', $mode = 'default')
	{
		$userIds = array();
		foreach ($courses as $key => $course) {
			$userIds = array_merge($userIds, $course['teacherIds']);

			$classrooms = array();
			if ($this->isPluginInstalled("Classroom")) {
				$classrooms=$this->getClassroomService()->findClassroomsByCourseId($course['id']);

				$classroomIds=ArrayToolkit::column($classrooms,'classroomId');

				$courses[$key]['classroomCount']=count($classroomIds);

				if(count($classroomIds)>0){

					$classroom=$this->getClassroomService()->getClassroom($classroomIds[0]);
					$courses[$key]['classroom']=$classroom;
				}
			}
		}
		
		$users = $this->getUserService()->findUsersByIds($userIds);
		
		return $this->render("TopxiaWebBundle:Course:courses-block-{$view}.html.twig", array(
			'courses' => $courses,
			'users' => $users,
			'classrooms'=>$classrooms,
			'mode' => $mode,
		));
	}

	public function selectAction(Request $request)
	{	
		$url="";
		$type="";
		$classroomId=0;

		if($request->query->get('url')){

			$url=$request->query->get('url');
		}

		if($request->query->get('type')){

			$type=$request->query->get('type');
		}

		if($request->query->get('classroomId')){

			$classroomId=$request->query->get('classroomId');
		}


		$conditions = array(
			'status' => 'published'
		);

		$paginator = new Paginator(
			$this->get('request'),
			$this->getCourseService()->searchCourseCount($conditions)
			, 5
		);

		$courses = $this->getCourseService()->searchCourses(
			$conditions, 'latest',
			$paginator->getOffsetCount(),
			$paginator->getPerPageCount()
		);

		$courseIds=ArrayToolkit::column($courses, 'id');
		$unEnabledCourseIds =$this->getClassroomCourseIds($request,$courseIds);

		$userIds = array();
		foreach ($courses as &$course) {
			$course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
			$userIds = array_merge($userIds, $course['teacherIds']);
		}

		$users = $this->getUserService()->findUsersByIds($userIds);

		return $this->render("TopxiaWebBundle:Course:course-select.html.twig", array(
			'users'=>$users,
			'url'=>$url,
			'courses'=>$courses,
			'type'=>$type,
			'unEnabledCourseIds'=>$unEnabledCourseIds,
			'classroomId'=>$classroomId,
			'paginator'=>$paginator
		));
	}

	private function getClassroomCourseIds($request,$courseIds)
	{	
		$unEnabledCourseIds=array();
		if($request->query->get('type') !="classroom")
			return $unEnabledCourseIds;

		if ($this->isPluginInstalled("Classroom")) {
			
			$classroomId=$request->query->get('classroomId');

	        foreach ($courseIds as $key => $value) {
	        	$course=$this->getCourseService()->getCourse($value);
	        	$classrooms = $this->getClassroomService()->findClassroomsByCourseId($value);
	        	if($course && count($classrooms)==0){
	        		unset($courseIds[$key]);
	        	}

	        }
	    }
        $unEnabledCourseIds = $courseIds;

        return $unEnabledCourseIds;
	}

    public function searchAction(Request $request)
    {	
        $key = $request->request->get("key");
        $classroomId=0;

        $conditions = array( "title"=>$key );
        $conditions['status'] = 'published';

		if($request->query->get('classroomId')){

			$classroomId=$request->query->get('classroomId');
		}

		$courses = $this->getCourseService()->searchCourses(
			$conditions, 'latest',
			0,
			5
		);

		$courseIds=ArrayToolkit::column($courses, 'id');
		$unEnabledCourseIds =$this->getClassroomCourseIds($request,$courseIds);

		$userIds = array();
		foreach ($courses as &$course) {
			$course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
			$userIds = array_merge($userIds, $course['teacherIds']);
		}

		$users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:Course:course-select-list.html.twig', array(
			'users'=>$users,
			'courses'=>$courses,
			'unEnabledCourseIds'=>$unEnabledCourseIds
		));
    }

	public function relatedCoursesBlockAction($course)
	{   

		$courses = $this->getCourseService()->findCoursesByAnyTagIdsAndStatus($course['tags'], 'published', array('Rating' , 'DESC'), 0, 4);
		
		return $this->render("TopxiaWebBundle:Course:related-courses-block.html.twig", array(
			'courses' => $courses,
			'currentCourse' => $course
		));
	}

	public function rebuyAction(Request $request,$courseId)
	{
		$user = $this->getCurrentUser();

		$this->getCourseService()->removeStudent($courseId, $user['id']);

		return $this->redirect($this->generateUrl('course_show',array('id' => $courseId)));
	}

	private function getClassroomMembersByCourseId($id) {

		if ($this->isPluginInstalled("Classroom")) {
			$classrooms = $this->getClassroomService()->findClassroomsByCourseId($id);
			$classroomIds = ArrayToolkit::column($classrooms, "classroomId");
			$user=$this->getCurrentUser();

			$members = $this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomIds);
			return $members;
		}
		return array();
	}

	private function createCourseForm()
	{
		return $this->createNamedFormBuilder('course')
			->add('title', 'text')
			->getForm();
	}

	protected function getUserService()
	{
		return $this->getServiceKernel()->createService('User.UserService');
	}

	protected function getLevelService()
	{
		return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
	}

	protected function getVipService()
	{
		return $this->getServiceKernel()->createService('Vip:Vip.VipService');
	}

	private function getCourseService()
	{
		return $this->getServiceKernel()->createService('Course.CourseService');
	}

	private function getOrderService()
	{
		return $this->getServiceKernel()->createService('Course.CourseOrderService');
	}

	private function getCategoryService()
	{
		return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
	}

	private function getTagService()
	{
		return $this->getServiceKernel()->createService('Taxonomy.TagService');
	}

	private function getReviewService()
	{
		return $this->getServiceKernel()->createService('Course.ReviewService');
	}

    	private function getHomeworkService()
    	{
        		return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    	} 

    	private function getExerciseService()
    	{
        		return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
    	}

	private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getUploadFileService()
    {
	return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}