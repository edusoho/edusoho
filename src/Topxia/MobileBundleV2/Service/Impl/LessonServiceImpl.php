<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\LessonService;
use Topxia\Common\ArrayToolkit;

class LessonServiceImpl extends BaseService implements LessonService
{

	public function getLessonMaterial()
	{
		$lessonId = $this->getParam("lessonId");
		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);
		$lessonMaterials = $this->controller->getMaterialService()->findLessonMaterials($lessonId, $start, 1000);
		$files = $this->controller->getUploadFileService()->findFilesByIds(ArrayToolkit::column($lessonMaterials, 'fileId'));
		
		return array(
			"start"=>$start,
			"limit"=>$limit,
			"total"=>1000,
			"data"=>$this->filterMaterial($lessonMaterials, $files)
			);
	}

	private function filterMaterial($lessonMaterials, $files)
	{
		$newFiles = array();
		foreach ($files as $key => $file) {
			$newFiles[$file['id']] = $file;
		}

		return array_map(function($lessonMaterial) use ($newFiles){
			$lessonMaterial['createdTime'] = date('c', $lessonMaterial['createdTime']);
			$field = $lessonMaterial['fileId'];
			$lessonMaterial['fileMime'] = $newFiles[$field]['type'];
			return $lessonMaterial;
		}, $lessonMaterials);
	}

	public function downMaterial()
	{
		$courseId = $this->getParam("courseId");
		$materialId = $this->getParam("materialId");
		list($course, $member) = $this->controller->getCourseService()->tryTakeCourse($courseId);

        		if ($member && !$this->controller->getCourseService()->isMemberNonExpired($course, $member)) {
            		return "course_materials";
        		}

        		if ($member && $member['levelId'] > 0) {
            		if ($this->controller->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
                				return "course_show";
            		}
        		}

        		$material = $this->controller->getMaterialService()->getMaterial($courseId, $materialId);
        		if (empty($material)) {
            		throw "createNotFoundException";
        		}		

        		$file = $this->controller->getUploadFileService()->getFile($material['fileId']);
        		if (empty($file)) {
            		throw "createNotFoundException";
        		}

        		if ($file['storage'] == 'cloud') {
            		$factory = new CloudClientFactory();
            		$client = $factory->createClient();
            		$client->download($client->getBucket(), $file['hashId'], 3600, $file['filename']);
        		} else {
            		return $this->createPrivateFileDownloadResponse($request, $file);
        		}
	}

	private function createPrivateFileDownloadResponse(Request $request, $file)
    	{

        		$response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        		$response->trustXSendfileTypeHeader();

        		$file['filename'] = urlencode($file['filename']);
        		if (preg_match("/MSIE/i", $request->headers->get('User-Agent'))) {
            		$response->headers->set('Content-Disposition', 'attachment; filename="'.$file['filename'].'"');
        		} else {
            		$response->headers->set('Content-Disposition', "attachment; filename*=UTF-8''".$file['filename']);
        		}

        		$mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);
        		if ($mimeType) {
            		$response->headers->set('Content-Type', $mimeType);
        		}

        		return $response;
    	}	

    	public function learnLesson()
    	{
    		$courseId = $this->getParam("courseId");
		$lessonId = $this->getParam("lessonId");
    		$user = $this->controller->getuserByToken($this->request);
        		if (!$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录！");
        		}

        		$this->controller->getCourseService()->finishLearnLesson($courseId, $lessonId);

        		return "finished";
    	}

    	public function getLearnStatus()
    	{
    		$user = $this->controller->getuserByToken($this->request);
		$courseId = $this->getParam("courseId");

		if ($user->isLogin()) {
			$learnStatuses = $this->controller->getCourseService()->getUserLearnLessonStatuses($user['id'], $courseId);
		} else {
			$learnStatuses = array();
		}

		return $learnStatuses;
    	}

    	public function unLearnLesson()
    	{
    		$courseId = $this->getParam("courseId");
		$lessonId = $this->getParam("lessonId");
    		$user = $this->controller->getuserByToken($this->request);
        		if (!$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录！");
        		}

        		$this->controller->getCourseService()->cancelLearnLesson($courseId, $lessonId);

        		return "learning";
    	}

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

		$member = $this->controller->getCourseService()->getCourseMember($courseId, $user['id']);
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