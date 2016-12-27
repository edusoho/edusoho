<?php
namespace AppBundle\Controller;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

abstract class CourseBaseController extends BaseController
{
    protected function tryGetCourseSetAndCourse($id)
    {
        $course = $this->getCourseService()->getCourse($id);
        if (empty($course)) {
            throw $this->createNotFoundException('Course#{$id} Not Found');
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        if (empty($courseSet)) {
            throw $this->createNotFoundException("CourseSet#{$course['courseSetId']} Not Found");
        }

        return array($courseSet, $course);
    }

    protected function getCourseMember($request, $course)
    {
        $previewAs = $request->query->get('previewAs');
        $user      = $this->getCurrentUser();
        $member    = $user['id'] ? $this->getMemberService()->getCourseMember($course['id'], $user['id']) : null;
        return $this->previewAsMember($previewAs, $member, $course);
    }

    protected function buildCourseLayoutData($request, $courseId)
    {
        $course    = $this->getCourseService()->getCourse($courseId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        if (empty($course)) {
            throw $this->createNotFoundException('Course Not Found');
        }

        $member = $this->getCourseMember($request, $course);

        return array($courseSet, $course, $member);
    }

    protected function tryBuildCourseLayoutData($request, $courseId)
    {
        list($courseSet, $course, $member) = $this->buildCourseLayoutData($request, $courseId);
        $response                          = null;

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $response = $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            $response = $this->createMessageResponse('info', '您还不是课程《'.$course['title'].'》的学员，请先购买或加入学习。', null, 3000, $this->generateUrl('course_set_show', array('id' => $id)));
        }

        return array($courseSet, $course, $member, $response);
    }

    protected function previewAsMember($as, $member, $course)
    {
        $user = $this->getCurrentUser();

        if (empty($user->id)) {
            return null;
        }

        if (in_array($as, array('member', 'guest'))) {
            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
