<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\StringToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;

class CourseController extends BaseController
{	
	public function favoriteAction(Request $request, $id)
	{
		$this->getCustomCourseService()->favoriteCourse($id);
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
		$this->getStatusService()->publishStatus(array(
			'type' => 'start_learn_lesson',
			'objectType' => 'lesson',
			'objectId' => $lessonId,
			'properties' => array(
				'course' => $this->simplifyCousrse($course),
				'lesson' => $this->simplifyLesson($lesson),
			)
		));
		return $this->render('TopxiaWebBundle:Course:learn.html.twig', array(
			'course' => $course,
		));
	}

	public function showAction(Request $request, $id)
	{
		$course = $this->getCourseService()->getCourse($id);
        $code = 'ChargeCoin';
        $ChargeCoin = $this->getAppService()->findInstallApp($code);
        
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
		// if ($member && empty($member['locked'])) {
		// 	$learnStatuses = $this->getCourseService()->getUserLearnLessonStatuses($user['id'], $course['id']);
		// 	//判断用户deadline到了，但是还是限免课程，将用户deadline延长
		// 	if( $member['deadline'] < time() && !empty($course['freeStartTime']) && !empty($course['freeEndTime']) && $course['freeEndTime'] >= time()) {
		// 		$member = $this->getCourseService()->updateCourseMember($member['id'], array('deadline'=>$course['freeEndTime']));
		// 	}

		// 	return $this->render("TopxiaWebBundle:Course:dashboard.html.twig", array(
		// 		'course' => $course,
		// 		'type' => $course['type'],
		// 		'member' => $member,
		// 		'items' => $items,
		// 		'learnStatuses' => $learnStatuses,
		// 		'currentTime' => $currentTime,
		// 		'weeks' => $weeks,
		// 		'files' => ArrayToolkit::index($files,'id'),
		// 		'ChargeCoin'=> $ChargeCoin
		// 	));
		// }
		
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
			'ChargeCoin'=> $ChargeCoin
		));
	}

	public function lessonBlockAction(Request $request, $id)
	{
		$course = $this->getCourseService()->getCourse($id);
		$lessons=$this->getCourseService()->getCourseLessons($id);
		$lessons=ArrayToolkit::group($lessons,'chapterId');
		$chapters=$this->getCustomCourseService()->findCourseChaptersByType($id,'chapter');
		$units=$this->getCustomCourseService()->findCourseChaptersByType($id,'unit');
		$units=ArrayToolkit::group($units,'parentId');
		return $this->render("TopxiaWebBundle:Course:course-lesson-list.html.twig", array(
			'chapters'=>$chapters,
			'units'=>$units,
			'lessons'=>$lessons
		));
	}


	public function previewAction(Request $request,$id)
	{
		$course = $this->getCourseService()->getCourse($id);
        $user = $this->getCurrentUser();
		$lessons=$this->getCourseService()->getCourseLessons($id);
		$lesson=array();
		foreach ($lessons as $le) {
			if ($le['type']=='video' && empty($lesson)) {
				$lesson=$le;
			}
		}

        if (empty($lesson)) {
            throw $this->createNotFoundException();
        }

        if (!empty($course['status']) && $course['status'] == 'closed') {
            return $this->render('TopxiaWebBundle:CourseLesson:preview-notice-modal.html.twig',array('course' => $course));
        }

        if (empty($lesson['free'])) {
            if (!$user->isLogin()) {
                throw $this->createAccessDeniedException();
            }
            return $this->forward('TopxiaWebBundle:CourseOrder:buy', array('id' => $id), array('preview' => true));
        }else{
            $allowAnonymousPreview = $this->setting('course.allowAnonymousPreview', 1);
            if (empty($allowAnonymousPreview) && !$user->isLogin()) {
                throw $this->createAccessDeniedException();
            }
        }

        if ($lesson['type'] == 'video' and $lesson['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                $factory = new CloudClientFactory();
                $client = $factory->createClient();
                $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);

                if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                    $token = $this->getTokenService()->makeToken('hlsvideo.view', array('data' => $lessonId, 'times' => 1, 'duration' => 3600));
                    $hlsKeyUrl = $this->generateUrl('course_lesson_hlskeyurl', array('courseId' => $lesson['courseId'], 'lessonId' => $lesson['id'], 'token' => $token['token']), true);
                    $headLeaderInfo = $this->getHeadLeaderInfo();
                    $hls = $client->generateHLSEncryptedListUrl($file['convertParams'], $file['metas2'], $hlsKeyUrl, $headLeaderInfo['headLeaders'], $headLeaderInfo['headLeaderHlsKeyUrl'], 3600);
                } else {
                    $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                }

            }

        } else if ($lesson['mediaSource'] == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $lesson['mediaUri'] = "http://player.youku.com/embed/{$matches[1]}";
                $lesson['mediaSource'] = 'iframe';
            } else {
                $lesson['mediaUri'] = $lesson['mediaUri'];
            }
        } else if ($lesson['mediaSource'] == 'tudou'){
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $lesson['mediaUri'] = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
                $lesson['mediaSource'] = 'iframe';
            } else {
                $lesson['mediaUri'] = $lesson['mediaUri'];
            }
        }
        return $this->render('TopxiaWebBundle:Course:lesson-preview.html.twig', array(
            'user' => $user,
            'course' => $course,
            'lesson' => $lesson,
            'hlsUrl' => (isset($hls) and is_array($hls) and !empty($hls['url'])) ? $hls['url'] : '',
        ));
	}
































	public function headerAction(Request $request,$id)
	{
		$course=$this->getCourseService()->getCourse($id);
		if(empty($course)){
			throw $this->createNotFoundException("课程不存在，或已删除。");
		}
		$user = $this->getCurrentUser();
		$tags = $this->getTagService()->findTagsByIds($course['tags']);
		$member = $user ? $this->getCourseService()->getCourseMember($course['id'], $user['id']) : null;
		$previewAs = $request->query->get('previewAs');
		$member = $this->previewAsMember($previewAs, $member, $course);
		$hasFavorited = $this->getCourseService()->hasFavoritedCourse($id);
		
		$checkMemberLevelResult = $courseMemberLevel = null;
		if ($this->setting('vip.enabled')) {
			$courseMemberLevel = $course['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($course['vipLevelId']) : null;
			if ($courseMemberLevel) {
				$checkMemberLevelResult = $this->getVipService()->checkUserInMemberLevel($user['id'], $courseMemberLevel['id']);
			}
		}
		return $this->render("TopxiaWebBundle:Course:course-header.html.twig", array(
			'course' => $course,
			'tags' => $tags,
			'member' => $member,
			'hasFavorited' => $hasFavorited,
			'courseMemberLevel' => $courseMemberLevel,
			'checkMemberLevelResult' => $checkMemberLevelResult
		));
	}

	public function relatedArticlesAction(Request $request,$id)
	{
		$course=$this->getCourseService()->getCourse($id);
		$tagIds = $course['tags'];
        $articles = $this->getArticleService()->findPublishedArticlesByTagIdsAndCount($tagIds,6);
        return $this->render("TopxiaWebBundle:Course:course-relatedArticles.html.twig", array(
			'articles'=>$articles
		));
	}

    private function convertFiltersToConditions($course, $filters)
    {
        $conditions = array('courseId' => $course['id']);
        switch ($filters['type']) {
            case 'question':
                $conditions['type'] = 'question';
                break;
            case 'elite':
                $conditions['isElite'] = 1;
                break;
            default:
                break;
        }
        return $conditions;
    }

    private function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['type'] = $request->query->get('type');
        if (!in_array($filters['type'], array('all', 'question', 'elite'))) {
            $filters['type'] = 'all';
        }
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('created', 'posted', 'createdNotStick', 'postedNotStick'))) {
            $filters['sort'] = 'posted';
        }
        return $filters;
    }

	private function simplifyCousrse($course)
	{
		return array(
			'id' => $course['id'],
			'title' => $course['title'],
			'picture' => $course['middlePicture'],
			'type' => $course['type'],
			'rating' => $course['rating'],
			'about' => StringToolkit::plain($course['about'], 100),
			'price' => $course['price'],
		);
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

	protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

	private function getCustomCourseSearcheService(){
		return $this->getServiceKernel()->createService('Custom:Course.CourseSearchService');
	}
	private function getCustomCourseService(){
		return $this->getServiceKernel()->createService('Custom:Course.CourseService');
	}
	private function getCourseService()
	{
		return $this->getServiceKernel()->createService('Course.CourseService');
	}
	private function getFavoriteDao ()
	{
	    return $this->createDao('Course.FavoriteDao');
	}
	private function getStatusService()
	{
	return $this->createService('User.StatusService');
	}

	protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getUploadFileService()
    {
	return $this->getServiceKernel()->createService('File.UploadFileService');
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

	private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    protected function getLevelService()
	{
		return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
	}

	protected function getVipService()
	{
		return $this->getServiceKernel()->createService('Vip:Vip.VipService');
	}

}