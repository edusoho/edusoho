<?php
namespace Topxia\WebBundle\Controller;

abstract class CourseBaseController extends BaseController
{
    protected function buildCourseLayoutData($request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        if (empty($course)) {
            throw $this->createNotFoundException("课程不存在");
        }

        $previewAs = $request->query->get('previewAs');
        $user      = $this->getCurrentUser();
        $member    = $user ? $this->getCourseService()->getCourseMember($course['id'], $user['id']) : null;

        $member = $this->previewAsMember($previewAs, $member, $course);

        return array($course, $member);
    }

    protected function buildLayoutDataWithTakenAccess($request, $id)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $id);
        $response              = null;

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $response = $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            $response = $this->createMessageResponse('info', "您还不是课程《{$course['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
        }

        return array($course, $member, $response);
    }

    protected function previewAsMember($as, $member, $course)
    {
        $user = $this->getCurrentUser();

        if (empty($user->id)) {
            return null;
        }

        if (in_array($as, array('member', 'guest'))) {
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $member = array(
                    'id'          => 0,
                    'courseId'    => $course['id'],
                    'userId'      => $user['id'],
                    'levelId'     => 0,
                    'learnedNum'  => 0,
                    'isLearned'   => 0,
                    'seq'         => 0,
                    'isVisible'   => 0,
                    'orderId'     => 0,
                    'joinedType'  => 'course',
                    'role'        => 'teacher',
                    'fake'        => true,
                    'locked'      => 0,
                    'createdTime' => time(),
                    'deadline'    => 0
                );
            }

            if (empty($member) || $member['role'] != 'teacher') {
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

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
