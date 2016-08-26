<?php 
namespace Custom\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\CourseController as BaseCourseController;

class CourseController extends BaseCourseController
{
    public function learnAction(Request $request, $id)
    {
        $user      = $this->getCurrentUser();
        $starttime = $request->query->get('starttime', '');

        if (!$user->isLogin()) {
            $request->getSession()->set('_target_path', $this->generateUrl('course_show', array('id' => $id)));

            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $course = $this->getCourseService()->getCourse($id);

        if (empty($course)) {
            throw $this->createNotFoundException("课程不存在，或已删除。");
        }

        if ($course['approval'] == 1 && ($user['approvalStatus'] != 'approved')) {
            return $this->createMessageResponse('info', "该课程需要通过实名认证，你还没有通过实名认证。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
        }

        if (!$this->getCourseService()->canTakeCourse($id)) {
            return $this->createMessageResponse('info', "您还不是课程《{$course['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
        }

        try {
            list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

            if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
                return $this->redirect($this->generateUrl('course_show', array('id' => $id)));
            }

            if ($member && $member['levelId'] > 0) {
                if ($this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
                    return $this->redirect($this->generateUrl('course_show', array('id' => $id)));
                }
            }
        } catch (Exception $e) {
            throw $this->createAccessDeniedException('抱歉，未发布课程不能学习！');
        }

        return $this->render('CustomWebBundle:Course:learn.html.twig', array(
            'course'    => $course,
            'starttime' => $starttime
        ));
    }
}