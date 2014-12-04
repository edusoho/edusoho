<?php
namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\CourseProcessor;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Response;

class CourseProcessorImpl extends BaseProcessor implements CourseProcessor
{
	public function getVersion()
	{
		var_dump("CourseProcessorImpl->getVersion");
		return $this->formData;
	}

	public function getCourseNotices()
	{
		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);
		$courseId = $this->getParam("courseId");
		if (empty($courseId)) {
			return array();
		}
		$announcements = $this->controller->getCourseService()->findAnnouncements($courseId, $start, $limit);
		return $this->filterAnnouncements($announcements);
	}

	public function getLessonNote()
	{
		$courseId = $this->getParam("courseId");
	    	$lessonId = $this->getParam("lessonId");

	    	$user = $this->controller->getUserByToken($this->request);
	    	if(!$user->isLogin()){
	    		return $this->createErrorResponse('not_login', "您尚未登录，不能查看笔记！");
	    	}

	    	$lessonNote =  $this->controller->getNoteService()->getUserLessonNote($user['id'], $lessonId);
	    	if (empty($lessonNote)) {
	    		return null;
	    	}
	    	$lesson = $this->controller->getCourseService()->getCourseLesson($courseId, $lessonId);
	    	$lessonNote['lessonTitle'] = $lesson['title'];
	    	$lessonNote['lessonNum'] = $lesson['number'];
	    	$content = $this->controller->convertAbsoluteUrl($this->request, $lessonNote['content']);;
	    	$content = $this->filterNote($content);
	    	$lessonNote['content'] = $content;
	    	return $lessonNote;
	}

	public function getCourseMember()
	{
		$courseId = $this->getParam("courseId");
		$user = $this->controller->getUserByToken($this->request);
		if (empty($courseId)) {
            		return null;
		}
		$member = $user->isLogin() ? $this->controller->getCourseService()->getCourseMember($courseId, $user['id']) : null;
     		$member = $this->previewAsMember($member, $courseId, $user);

     		if ($member && $member['locked']) {
            		return null;
     		}
     		$member = $this->checkMemberStatus($member);
     		return empty($member) ? new Response("null"): $member;
	}

	public function postThread()
	{
		$courseId = $this->getParam("courseId", 0);
		$threadId = $this->getParam("threadId", 0);

		$user = $this->controller->getUserByToken($this->request);
		if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录，不能回复！");
        }
        $thread = $this->controller->getThreadService()->getThread($courseId, $threadId);
        if (empty($thread)) {
        	return $this->createErrorResponse('not_thread', "问答不存在或已删除");
        }

		$content = $this->getParam("content", '');
		$content = $this->uploadImage($content);

		$formData = $this->formData;
		$formData['content'] = $content;
		unset($formData['imageCount']);
		$post = $this->controller->getThreadService()->createPost($formData);
		return $post;
	}

	/*
	 *更新回复
	 */ 
	public function updatePost(){
		$courseId = $this->getParam("courseId", 0);
		$threadId = $this->getParam("threadId", 0);
		$postId = $this->getParam('postId', 0);

		$user = $this->controller->getUserByToken($this->request);
		if (!$user->isLogin()) {
	        return $this->createErrorResponse('not_login', "您尚未登录，不能评价课程！");
	    }

	    // if (empty($thread)) {
	    //     return $this->createErrorResponse('not_thread', "问答不存在或已删除");
	    // }

	    if(!empty($postId)) {
	    	$post = $this->controller->getThreadService()->getPost($courseId, $postId);
	    	if(empty($post)){
				return $this->createErrorResponse('postId_not_exist', "postId不存在！");
	    	}
		}else{
			return $this->createErrorResponse('wrong_postId_param', "postId参数错误！");
		}

		$content = $this->getParam("content", '');
		if(empty($content)){
			return $this->createErrorResponse('wrong_content_param', "回复内容不能为空！");
		}

		$content = $this->uploadImage($content);

		$formData = $this->formData;
		$formData['content'] = $content;
		unset($formData['imageCount']);

		$post = $this->controller->getThreadService()->updatePost($courseId, $postId, $formData);
		return $post;
	}

	/**
	* add need param (courseId, lessonId, title, content, type="question")
	* update need param (courseId, threadId, title, content, type)
	*/
	public function updateThread(){
		$courseId = $this->getParam("courseId", 0);
		$threadId = $this->getParam("threadId", 0);
		$title = $this->getParam("title", "");
		$content = $this->getParam("content","");
		$action = $this->getParam("action", "update");
		$imageCount = $this->getParam("imageCount", 0);

		$user = $this->controller->getUserByToken($this->request);
		if (!$user->isLogin()) {
			return $this->createErrorResponse('not_login', '您尚未登录，修改该课时');
		}

		if($imageCount > 0){
			$content = $this->uploadImage($content);
		}

		$formData = $this->formData;
		$formData['content'] = $content;
		unset($formData['imageCount']);
		unset($formData['action']);
		unset($formData['threadId']);

		if ($action == "add") {
			return $this->controller->getThreadService()->createThread($formData);
		} else {
			$fields = array("title" => $title, "content" => $content);
			return $this->controller->getThreadService()->updateThread($courseId, $threadId, $fields);
		}
	}

	private function uploadImage($content)
	{
		$url = "none";
		$urlArray = array();
		$files = $file = $this->request->files;
		foreach ($files as $key => $value) {
			try {
				$group = $this->getParam("group", 'course');
				$record = $this->getFileService()->uploadFile($group, $value);
				$url = $this->controller->get('topxia.twig.web_extension')->getFilePath($record['uri']);
				
			} catch (\Exception $e) {
				$url = "error";
			}
			$urlArray[$key] = $url;
		}

		$baseUrl = $this->request->getSchemeAndHttpHost();
		$content = preg_replace_callback('/src=[\'\"](.*?)[\'\"]/', function($matches) use ($baseUrl, $urlArray) {
			if(strpos($matches[1], "http") !== false){
				return "src=\"$matches[1]\"";
			}
			else{
				return "src=\"{$urlArray[$matches[1]]}\"";
			}
		}, $content);
        return $content;
	}

	public function commitCourse()
	{
		$courseId = $this->getParam("courseId", 0);
		$user = $this->controller->getUserByToken($this->request);

        		if (!$user->isLogin()) {
            		return $this->createErrorResponse($request, 'not_login', "您尚未登录，不能评价课程！");
        		}

        		$course = $this->controller->getCourseService()->getCourse($courseId);
        		if (empty($course)) {
            		return $this->createErrorResponse('not_found', "课程#{$courseId}不存在，不能评价！");
        		}

        		if (!$this->controller->getCourseService()->canTakeCourse($course)) {
            		return $this->createErrorResponse('access_denied', "您不是课程《{$course['title']}》学员，不能评价课程！");
        		}

        		$review = array();
        		$review['courseId'] = $course['id'];
        		$review['userId'] = $user['id'];
        		$review['rating'] = (float)$this->getParam("rating", 0);
        		$review['content'] = $this->getParam("content", '');

        		$review = $this->controller->getReviewService()->saveReview($review);
        		$review = $this->controller->filterReview($review);

        		return $review;
	}

	public function getCourseThreads()
	{
		$user = $this->controller->getUserByToken($this->request);
		$type = $this->getParam("type","question");
		$lessonId = $this->getParam("lessonId","0");

		if($lessonId == "0"){
			$conditions = array(
	            		'userId' => $user['id'],
	            		'type' => $type,
	        		);
		}else{
			$conditions = array('lessonId' => $lessonId,
								'type' => $type,);
		}

		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);
		$total = $this->controller->getThreadService()->searchThreadCount($conditions);

		$threads = $this->controller->getThreadService()->searchThreads(
    		$conditions,
    		'createdNotStick',
    		$start,
    		$limit
		);

		$courses = $this->controller->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
		return array(
		"start"=>$start,
		"limit"=>$limit,
		"total"=>$total,
		'threads'=>$this->filterThreads($threads, $courses, $user['id']),
		);
	}

	public function getCourseNotes()
	{
	    	$start = $this->getParam("start", 0);
	    	$limit = $this->getParam("limit", 10);
	    	$courseId = $this->getParam("courseId");

	    	$user = $this->controller->getUserByToken($this->request);
	    	if(!$user->isLogin()){
	    		return $this->createErrorResponse('not_login', "您尚未登录，不能查看笔记！");
	    	}
	    	$conditions = array(
	            	'userId' => $user['id'],
	            	'courseId' => $courseId,
	            	'noteNumGreaterThan' => 0
	        	);

	    	$courseNotes =  $this->controller->getNoteService()->searchNotes($conditions, 'created', $start, $limit);
	    	$lessons = $this->controller->getCourseService()->findLessonsByIds(ArrayToolkit::column($courseNotes, 'lessonId'));
	    	for ($i=0; $i < count($courseNotes); $i++) { 
	    		$courseNote = $courseNotes[$i];
	    		$lesson = $lessons[$courseNote['lessonId']];
	    		$courseNote['lessonTitle'] = $lesson['title'];
	    		$courseNote['lessonNum'] = $lesson['number'];
	    		$content = $this->controller->convertAbsoluteUrl($this->request, $courseNote['content']);
	    		$content = $this->filterNote($content);
	    		$courseNote['content'] = $content;
	    		$courseNotes[$i] = $courseNote;
	    	}
	    	return $courseNotes;
	}

	private function filterNote($note)
	{
		return  preg_replace_callback('/<img [^>]+\\/?>/', function($matches) {
			return "<p>" . $matches[0] . "</p>";
		}, $note);
	}

	public function getNoteList(){
	    	$user = $this->controller->getUserByToken($this->request);
	    	$start = $this->getParam("start", 0);
	    	$limit = $this->getParam("limit", 10);

	    	if(!$user->isLogin()){
	    		return $this->createErrorResponse('not_login', "您尚未登录，不能查看笔记！");
	    	}

	     	$conditions = array(
	            	'userId' => $user['id'],
	            	'noteNumGreaterThan' => 0
	        	);

	        	$courseMembers = $this->controller->getCourseService()->searchMember($conditions,$start,$limit);
	        	$courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($courseMembers, 'courseId'));
	        	$noteInfos = array();
	        	for ($i=0; $i < count($courseMembers); $i++) { 
	        		$courseMember = $courseMembers[$i];
	        		$course = $courses[$courseMember['courseId']];
	        		$noteNum = $courseMember['noteNum'];
	        		if (empty($noteNum)) {
	        			continue;
	        		}
	        		$noteListByOneCourse = $this->controller->getNoteService()->findUserCourseNotes($user['id'],$courseMember['courseId']);
	        		foreach ($noteListByOneCourse as $value) {
	        			$lessonInfo = $this->controller->getCourseService()->getCourseLesson($value['courseId'],$value['lessonId']);
	        			$lessonStatus = $this->controller->getCourseService()->getUserLearnLessonStatus($user['id'], $value['courseId'],$value['lessonId']);
	        			$noteInfos[] = array(
		        			"coursesId"=>$courseMember['courseId'],
		        			"courseTitle"=>$course['title'],
		        			"noteLastUpdateTime"=>date('c',$courseMember['noteLastUpdateTime']),
		        			"lessonId"=>$lessonInfo['id'],
		        			"lessonTitle"=>$lessonInfo['title'],
		        			"learnStatus"=>$lessonStatus,
		        			"content"=>$this->controller->convertAbsoluteUrl($this->request, $value['content']),
		        			"createdTime"=>date('c', $value['createdTime']),
		        			"noteNum"=>$noteNum,
		        			"largePicture"=>$this->controller->coverPath($course["largePicture"], 'course-large.png'),
		        			);
	        		}
	        	}
	        	/*
	    	$noteList = array();
	    	$index = 0;
	    	for($i = 0;$i < count($courseMember);$i++){
	    		$noteListByOneCourse =  $this->controller->getNoteService()->findUserCourseNotes($user['id'],$courseMember[$i]['courseId']);
	    		foreach ($noteListByOneCourse as $key => $value) {
		    		$courseId = $value['courseId'];
		    		$lessonId = $value['lessonId'];
		    		$courseInfo = $this->controller->getCourseService()->getCourse($courseId);
		    		$lessonInfo = $this->controller->getCourseService()->getCourseLesson($courseId, $lessonId);
		    		$value["title"] = $courseInfo["title"];
		    		$value["largePicture"] = $this->controller->coverPath($courseInfo["largePicture"], 'course-large.png');
		    		$value["lessonName"] = $lessonInfo["title"];
		    		$value["number"] = $lessonInfo["number"];
		    		$noteList[$index++] = $value;
	    		}
	    	}

	    	foreach ($noteList as $key => $value) {
	    		$sort_course[$key] = $value['courseId'];
	    		$sort_lesson[$key] = $value['lessonId'];
	    	}
	    	array_multisort($sort_course,SORT_ASC,$sort_lesson,SORT_ASC,$noteList);
	    	*/
    		return $noteInfos;
    	}

    public function AddNote(){
    	$courseId = $this->getParam("courseId", 0);
    	$lessonId = $this->getParam("lessonId", 0);
    	$content = $this->getParam("content", "");

		$user = $this->controller->getUserByToken($this->request);
    	if(!$user->isLogin()){
    		return $this->createErrorResponse('not_login', "您尚未登录，不能查看笔记！");
    	}

    	$noteInfo = array(
            'content' => $content,
            'lessonId' => $lessonId,
            'courseId' => $courseId,
        );

		$content = $this->getParam("content", '');
		if(empty($content)){
			return $this->createErrorResponse('wrong_content_param', "笔记内容不能为空！");
		}

		$content = $this->uploadImage($content);
		$noteInfo['content'] = $content;

    	$result = $this->controller->getNoteService()->saveNote($noteInfo);
    	$result['createdTime'] = date('c',$result['createdTime']);
    	$result['updatedTime'] = date('c',$result['updatedTime']);

    	return $result;
    }

    public function DeleteNote(){
    	$id = $this->getParam("id", 0);
    	$user = $this->controller->getUserByToken($this->request);
    	if(!$user->isLogin()){
    		return $this->createErrorResponse('not_login', "您尚未登录，不能查看笔记！");
    	}
    	return $this->controller->getNoteService()->deleteNote($id);
    }

	private function filterThreads($threads, $courses ,$userId)
	{
		if (empty($threads)) {
            return array();
        }

        for ($i=0; $i < count($threads); $i++) { 
        			$thread = $threads[$i];
        	if (!isset($courses[$thread["courseId"]])) {
				unset($threads[$i]);
				continue;
			}
			$course = $courses[$thread['courseId']];
			if($thread["lessonId"]!=0){
				$lessonInfo = $this->controller->getCourseService()->findLessonsByIds(array($thread["lessonId"]));
				$thread["number"] =  $lessonInfo[$thread["lessonId"]]["number"];
			}else{
				$thread["number"] = 0;
			}
        	$threads[$i] = $this->filterThread($thread, $course, $this->controller->getUserService()->getUser($userId));
        }
        return $threads;
	}

	private function filterThread($thread, $course, $user)
	{
		$thread["courseTitle"] = $course["title"];

		$thread['coursePicture'] = $this->controller->coverPath($course["largePicture"], 'course-large.png');

		$isTeacherPost = $this->controller->getThreadService()->findThreadElitePosts($course['id'], $thread['id'], 0, 100);
		$thread['isTeacherPost'] = empty($isTeacherPost) ? false : true;
		$user['smallAvatar'] = $this->controller->getContainer()->get('topxia.twig.web_extension')->getFilePath($user['smallAvatar'], 'course-large.png', true);
        $user['mediumAvatar'] = $this->controller->getContainer()->get('topxia.twig.web_extension')->getFilePath($user['mediumAvatar'], 'course-large.png', true);
        $user['largeAvatar'] = $this->controller->getContainer()->get('topxia.twig.web_extension')->getFilePath($user['largeAvatar'], 'course-large.png', true);
		$thread['user'] = $user;
		$thread['createdTime'] = date('c', $thread['createdTime']);
		$thread['latestPostTime'] = date('c', $thread['latestPostTime']);

		return $thread;
	}

	public function getThreadPost()
	{
		$courseId = $this->getParam("courseId", 0);
		$threadId = $this->getParam("threadId", 0);
		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);

		$user = $this->controller->getUserByToken($this->request);
		if (!$user->isLogin()) {
			return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
		}

		$total = $this->controller->getThreadService()->getThreadPostCount($courseId, $threadId);
		$posts = $this->controller->getThreadService()->findThreadPosts($courseId, $threadId, 'elite', $start, $limit);
		$users = $this->controller->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

		$controller = $this;
		$posts = array_map(function($post) use ($controller) {
            $post['content'] = $controller->controller->convertAbsoluteUrl($controller->request, $post['content']);
            return $post;
        }, $posts);

		return array(
			"start"=>$start,
			"limit"=>$limit,
			"total"=>$total,
			"data"=>$this->filterPosts($posts, $this->controller->filterUsers($users))
			);			
	}

	public function getThread()
	{
		$courseId = $this->getParam("courseId", 0);
		$threadId = $this->getParam("threadId", 0);
		$user = $this->controller->getUserByToken($this->request);
		if (!$user->isLogin()) {
			return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
		}

		$thread = $this->controller->getThreadService()->getThread($courseId, $threadId);
		if (empty($thread)) {
			return $this->createErrorResponse('no_thread', '没有找到指定问答!');
		}

		$course = $this->controller->getCourseService()->getCourse($thread['courseId']);
        $user = $this->controller->getUserService()->getUser($thread['userId']);
		return $this->filterThread($thread, $course, $user);
	}

	public function getThreadTeacherPost()
	{
		$courseId = $this->getParam("courseId", 0);
		$threadId = $this->getParam("threadId", 0);

		$user = $this->controller->getUserByToken($this->request);
		if (!$user->isLogin()) {
			return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
		}

		$posts = $this->controller->getThreadService()->findThreadElitePosts($courseId, $threadId, 0, 100);
		$users = $this->controller->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));
		
		return $this->filterPosts($posts, $this->controller->filterUsers($users));
	}

	private function filterPosts($posts, $users)
	{
		return array_map(function($post) use ($users){
			$post['user'] = $users[$post['userId']];
			$post['createdTime'] = date('c', $post['createdTime']);
			return $post;
		}, $posts);
	}

	public function getFavoriteCoruse()
	{
		$user = $this->controller->getUserByToken($this->request);
		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);

		$total = $this->controller->getCourseService()->findUserFavoritedCourseCount($user['id']);
		$courses = $this->controller->getCourseService()->findUserFavoritedCourses($user['id'], $start, $limit);

		return array(
			"start"=>$start,
			"limit"=>$limit,
			"total"=>$total,
			"data"=>$this->controller->filterCourses($courses)
			);
	}

	public function getCourseNotice()
	{
		$courseId = $this->getParam("courseId");
		if (empty($courseId)) {
			return array();
		}
		$announcements = $this->controller->getCourseService()->findAnnouncements($courseId, 0, 10);
		return $this->filterAnnouncements($announcements);
	}

	private function filterAnnouncements($announcements)
	{
		$controller = $this->controller;
		return array_map(function($announcement) use ($controller){
			unset($announcement["userId"]);
			unset($announcement["courseId"]);
			unset($announcement["updatedTime"]);
			$announcement["content"] = $controller->convertAbsoluteUrl($controller->request, $announcement["content"]);
			$announcement["createdTime"] = date('Y-m-d H:i:s', $announcement['createdTime']);
			return $announcement;
		}, $announcements);
	}

	private function filterAnnouncement($announcement)
	{
		return $this->filterAnnouncements(array($announcement));
	}

	public function getReviews()
	{
		$courseId = $this->getParam("courseId");

		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);
		$total = $this->controller->getReviewService()->getCourseReviewCount($courseId);
		$reviews = $this->controller->getReviewService()->findCourseReviews($courseId, $start, $limit);
		$reviews = $this->controller->filterReviews($reviews);
		return array(
			"start"=>$start,
			"limit"=>$limit,
			"total"=>$total,
			"data"=>$reviews
			);
	}


	public function favoriteCourse()
	{
        		$user = $this->controller->getUserByToken($this->request);
        		$courseId = $this->getParam("courseId");

        		if (empty($user) || !$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录，不能收藏课程！");
        		}

        		if (!$this->controller->getCourseService()->hasFavoritedCourse($courseId)) {
            		$this->controller->getCourseService()->favoriteCourse($courseId);
        		}

        		return true;
	}

	public function getTeacherCourses()
	{
		$userId = $this->getParam("userId");
		if (empty($userId)) {
			return array();
		}
		$courses = $this->controller->getCourseService()->findUserTeachCourses(
	            	$userId, 0, 10
	        	);
		$courses = $this->controller->filterCourses($courses);
		return $courses;
	}

	public function unFavoriteCourse()
	{
		$user = $this->controller->getUserByToken($this->request);
        		$courseId = $this->getParam("courseId");

        		if (empty($user) || !$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录，不能收藏课程！");
        		}

        		if (!$this->controller->getCourseService()->hasFavoritedCourse($courseId)) {
            		return $this->createErrorResponse('runtime_error', "您尚未收藏课程，不能取消收藏！");
        		}

        		$this->controller->getCourseService()->unfavoriteCourse($courseId);

        		return true;
	}

	public function vipLearn()
	{	
		if (!$this->controller->setting('vip.enabled')) {
            		return $this->createErrorResponse('error', "网校没有开启vip功能");
        		}
        		
        		$courseId = $this->getParam('courseId');
        		$user = $this->controller->getUserByToken($this->request);
        		if (!$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录，不能收藏课程！");
        		}
        		$this->controller->getCourseService()->becomeStudent($courseId, $user['id'], array('becomeUseMember' => true));
        		return true;
	}

	public function coupon()
	{
		$code = $this->getParam('code');
		$type = $this->getParam('type');
		$courseId = $this->getParam('courseId');
            	//判断coupon是否合法，是否存在跟是否过期跟是否可用于当前课程
            	$course = $this->controller->getCourseService()->getCourse($courseId);
            	$couponInfo = $this->getCouponService()->checkCouponUseable($code, $type, $courseId, $course['price']);
            
            	return $couponInfo;
	}

	public function unLearnCourse()
	{	
		$courseId = $this->getParam("courseId");
        		$user = $this->controller->getUserByToken($this->request);
        		list($course, $member) = $this->controller->getCourseService()->tryTakeCourse($courseId);

        		if (empty($member)) {
            		return $this->createErrorResponse('not_member', '您不是课程的学员或尚未购买该课程，不能退学。');
        		}
        		if (!empty($member['orderId'])) {
        			$order = $this->getOrderService()->getOrder($member['orderId']);
	        		if (empty($order)) {
	            		return $this->createErrorResponse( 'order_error', '订单不存在，不能退学。');
	            	}

        			$reason = $this->getParam("reason", "");
        			$amount = $this->getParam("amount", 0);
        			$refund = $this->getCourseOrderService()->applyRefundOrder(
        				$member['orderId'], $amount, array("type"=>"other", "note"=>$reason), $this->getContainer());
        			if (empty($refund) || $refund['status'] != "success") {
        				return false;
        			}
        			return true;
        		}
        		
        		$this->getCourseService()->removeStudent($course['id'], $user['id']);
        		return true;
	}

	public function getCourse()
	{	
		$user = $this->controller->getUserByToken($this->request);
		$courseId = $this->getParam("courseId");
		$course = $this->controller->getCourseService()->getCourse($courseId);

		if (empty($course)) {
            		return $this->createErrorResponse('not_found', "课程不存在");
		}

		$member = $user->isLogin() ? $this->controller->getCourseService()->getCourseMember($course['id'], $user['id']) : null;
     		$member = $this->previewAsMember($member, $courseId, $user);
     		if ($member && $member['locked']) {
            		return $this->createErrorResponse('member_locked', "会员被锁住，不能访问课程，请联系管理员!");
     		}
        		if ($course['status'] != 'published') {
        			if (!$user->isLogin()){
            			return $this->createErrorResponse('course_not_published', "课程未发布或已关闭。");
        			}
        			if (empty($member)) {
            			return $this->createErrorResponse('course_not_published', "课程未发布或已关闭。");
        			}
        			$deadline = $member['deadline'] ;
        			$createdTime = $member['createdTime'];

        			if ($deadline !=0 && ($deadline - $createdTime) < 0) {
            			return $this->createErrorResponse('course_not_published', "课程未发布或已关闭。");
        			}
        		}

        		$userFavorited = $user->isLogin() ? $this->controller->getCourseService()->hasFavoritedCourse($courseId) : false;
        		$vipLevels = array();
        		if ($this->controller->setting('vip.enabled')) {
	                $vipLevels = $this->controller->getLevelService()->searchLevels(array('enabled' => 1), 0, 100);
	            }
        		return array(
        			"course"=>$this->controller->filterCourse($course),
        			"userFavorited"=>$userFavorited,
        			"member"=>$this->checkMemberStatus($member),
        			"vipLevels"=>$vipLevels
        			);
	}

	public function searchCourse()
	{
		$search = $this->getParam("search", '');
		$conditions['title'] = $search;
		return $this->findCourseByConditions($conditions);
	}

	public function getCourses()
	{
		$categoryId = (int) $this->getParam("categoryId", 0);
		$conditions['categoryId'] = $categoryId;
		return $this->findCourseByConditions($conditions);
	}

	private function findCourseByConditions($conditions)
	{
		$conditions['status'] = 'published';
        		$conditions['type'] = 'normal';

		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);
		$total = $this->controller->getCourseService()->searchCourseCount($conditions);

		$sort = $this->getParam("sort", "latest");
		$conditions['sort'] = $sort;

        		$courses = $this->controller->getCourseService()->searchCourses($conditions, $sort, $start, $limit);
		$result = array(
			"start"=>$start,
			"limit"=>$limit,
			"total"=>$total,
			"data"=>$this->controller->filterCourses($courses)
			);
		return $result;
	}

	public function getLearnedCourse()
	{
		$user = $this->controller->getUserByToken($this->request);
		if (!$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录！");
        		}

		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);
        		$total = $this->controller->getCourseService()->findUserLeanedCourseCount($user['id']);
        		$courses = $this->controller->getCourseService()->findUserLeanedCourses($user['id'], $start, $limit);
        		
        		$result = array(
			"start"=>$start,
			"limit"=>$limit,
			"total"=>$total,
			"data"=> $this->controller->filterCourses($courses)
			);
		return $result;
	}

	public function getLearningCourse()
	{
		$user = $this->controller->getUserByToken($this->request);
		if (!$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录！");
        		}

		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);
        		$total = $this->controller->getCourseService()->findUserLeaningCourseCount($user['id']);
        		$courses = $this->controller->getCourseService()->findUserLeaningCourses($user['id'], $start, $limit);
        		
        		$result = array(
			"start"=>$start,
			"limit"=>$limit,
			"total"=>$total,
			"data"=> $this->controller->filterCourses($courses)
			);
		return $result;
	}


    public function getLearnStatus()
    {
        $courseId = $this->getParam("courseId");
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $course = $this->controller->getCourseService()->getCourse($courseId);
        $learnStatus = $this->controller->getCourseService()->getUserLearnLessonStatuses($user['id'], $courseId);
        if (!empty($course)) {
            $member = $this->controller->getCourseService()->getCourseMember($course['id'], $user['id']);
            $progress = $this->calculateUserLearnProgress($course, $member);
        } else {
            $course = array();
            $progress = array();
        }

        foreach ($learnStatus as $key => $value) {
        		if ($value == "finished") {
        			unset($learnStatus[$key]);
        		}
        }
        $keys = array_keys($learnStatus);
        $lessonId = end($keys);
        $lesson = $this->controller->getCourseService()->getCourseLesson($courseId, $lessonId);
        return array(
        	"data"=>$lesson,
            'progress'  => $progress
            );
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

    private function checkMemberStatus($member)	 
    {	 
	if ($member) {
		$deadline = $member['deadline'];	 
		if ($deadline == 0) {	 
			return $member;
		}	 
		$remain = $deadline - time();	 
		if ($remain <= 0) {	 
			$member['deadline'] = -1;
		} else {
			$member['deadline'] = $remain;
		}
	} 
	return $member;
    }
}