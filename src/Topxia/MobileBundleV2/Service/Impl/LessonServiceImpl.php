<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\LessonService;

class LessonServiceImpl extends BaseService implements LessonService
{
	public function getCourseLessons()
	{
		$token = $this->controller->getUserToken($this->request);
		$user = $this->controller->getUser();
		$courseId = $this->getParam("courseId");

		$lessons = $this->controller->getCourseService()->getCourseItems($courseId);
		$lessons = $this->controller->filterItems($lessons);
		if ($user->isLogin()) {
			$learnStatuses = $this->controller->getCourseService()->getUserLearnLessonStatuses($user['id'], $courseId);
		} else {
			$learnStatuses = array();
		}

		$lessons = $this->filterLessons($lessons);
		return array(
			"lessons"=>array_values($lessons),
			"learnStatuses"=>$learnStatuses
			);
	}

	public function getLesson()
	{
		$courseId = $this->getParam("courseId");
		$lessonId = $this->getParam("lessonId");
		if (empty($courseId)) {
			return $this->createErrorResponse('not_courseId', '课程信息不存在！');
		}

		$user = $this->controller->getuserByToken($this->request);
		$lesson = $this->controller->getCourseService()->getCourseLesson($courseId, $lessonId);

		if (empty($lesson)) {
			return $this->createErrorResponse('not_courseId', '课时信息不存在！');
		}

		$lesson = $this->coverLesson($lesson);
		if ($lesson['free'] == 1) {
			return $lesson;
		}

		if (!$user->isLogin()) {
			return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
		}

		$member = $this->controller->getCourseService()->getCourseMember($courseId, $user['id']) : null;
		$member = $this->previewAsMember($member, $courseId, $user);
		if ($member && in_array($member['role'], array("teacher", "student"))) {
			return $lesson;
		}
		return $this->createErrorResponse('not_student', '你不是该课程学员，请加入学习!');
	}

	private function coverLesson($lesson)
	{
		$lesson['createdTime'] = date('c', $lesson['createdTime']);
		$lesson['content'] = $this->wrapContent($lesson['content']);
		return $lesson;
	}

	private function wrapContent($content)
	{
		$content= $this->controller->convertAbsoluteUrl($this->request, $content);

		$render = $this->controller->render('TopxiaMobileBundleV2:Content:index.html.twig', array(
            		'content' => $content
        		));

		return $render->getContent();
	}

	private function filterLessons($lessons)
	{
		return array_map(function($lesson) {
            		$lesson['content'] = "";
            		return $lesson;
        		}, $lessons);
	}
}