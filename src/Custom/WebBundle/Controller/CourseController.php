<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\StringToolkit;
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

}