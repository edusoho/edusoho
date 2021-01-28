<?php

namespace AppBundle\Controller\Course;

use Biz\Course\CourseException;
use Biz\Course\CourseSetException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\CourseSetService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use AppBundle\Common\ArrayToolkit;

abstract class CourseBaseController extends BaseController
{
    protected function tryGetCourseSetAndCourse($id)
    {
        $course = $this->getCourseService()->getCourse($id);
        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        if (empty($courseSet)) {
            $this->createNewException(CourseSetException::NOTFOUND_COURSESET());
        }

        return array($courseSet, $course);
    }

    protected function buildCourseLayoutData(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $member = $this->getCourseMember($request, $course);

        return array($course, $member);
    }

    protected function tryBuildCourseLayoutData($request, $courseId)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $courseId);
        $response = null;
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $response = $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        } elseif (!$this->getCourseService()->canTakeCourse($course)) {
            $response = $this->createMessageResponse(
                'info',
                '您还不是课程《'.$courseSet['title'].'》的学员，请先购买或加入学习。',
                null,
                3000,
                $this->generateUrl('course_show', array('id' => $courseId))
            );
        }

        return array($course, $member, $response);
    }

    protected function getCourseMember(Request $request, $course)
    {
        $previewAs = $request->query->get('previewAs');
        $user = $this->getCurrentUser();
        $member = $user['id'] ? $this->getMemberService()->getCourseMember($course['id'], $user['id']) : null;

        return $this->previewAsMember($previewAs, $member, $course);
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
                    'id' => 0,
                    'courseId' => $course['id'],
                    'userId' => $user['id'],
                    'levelId' => 0,
                    'learnedNum' => 0,
                    'isLearned' => 0,
                    'seq' => 0,
                    'isVisible' => 0,
                    'orderId' => 0,
                    'joinedType' => 'course',
                    'role' => 'teacher',
                    'fake' => true,
                    'locked' => 0,
                    'createdTime' => time(),
                    'deadline' => 0,
                    'previewAs' => 1,
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

    protected function findCourseSetTagsByCourseSetId($courseSetId)
    {
        $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course-set', 'ownerId' => $courseSetId));
        $tags = ArrayToolkit::index($tags, 'id');

        return $tags;
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

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }
}
