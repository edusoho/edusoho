<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TimeMachine;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Course extends AbstractResource
{
    const DEFAULT_PAGING_LIMIT = 30;

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            $member = $this->getMemberService()->getCourseMember($course['id'], $user['id']);
        }

        if ($course['parentId'] > 0) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
            empty($classroom) || $course['classroom'] = $this->getClassroomService()->appendSpecInfo($classroom);
        }

        if (!empty($classroom) && empty($member)) {
            $this->joinCourseMemberByClassroomId($course['id'], $classroom['id']);
        }

        $assistants = $this->getMemberService()->findMembersByCourseIdAndRole($courseId, 'assistant');
        $course['assistantIds'] = ArrayToolkit::column($assistants, 'userId');

        $course['assistant'] = null;
        if (!empty($user['id'])) {
            $assistantStudent = $this->getAssistantStudentService()->getByStudentIdAndCourseId($user['id'], $courseId);
            if (!empty($assistantStudent)) {
                $course['assistantId'] = $assistantStudent['assistantId'];
                $this->getOCUtil()->single($course, ['assistantId']);
                $course['assistant'] = $this->getAssistantScrmQrCode($course['assistant']);
            }
        }

        $this->getOCUtil()->single($course, ['creator', 'teacherIds', 'assistantIds']);
        $this->getOCUtil()->single($course, ['courseSetId'], 'courseSet');

        if (!empty($member)) {
            $course['access'] = $this->getCourseService()->canLearnCourse($courseId);
        } else {
            $course['access'] = $this->getCourseService()->canJoinCourse($courseId);
        }

        $course = $this->convertFields($course);

        return $course;
    }

    protected function getAssistantScrmQrCode($assistant)
    {
        if (empty($assistant['scrmStaffId'])) {
            return $assistant;
        }

        $scrmBindQrCode = $this->generateScrmQrCode($assistant);
        if (!empty($scrmBindQrCode)) {
            $assistant['weChatQrCode'] = $scrmBindQrCode;
        }

        return $assistant;
    }

    protected function generateScrmQrCode($assistant)
    {
        $scrmBind = $this->getSCRMService()->isScrmBind();
        if (empty($scrmBind)) {
            return '';
        }

        $user = $this->setScrmData();
        if (!empty($user['scrmUuid'])) {
            return $this->getSCRMService()->getAssistantQrCode($assistant);
        }

        $url = $this->getScrmStudentBindUrl($assistant);
        if (empty($url)) {
            return '';
        }

        $token = $this->getTokenService()->makeToken(
            'qrcode',
            [
                'userId' => $user['id'],
                'data' => [
                    'url' => $url,
                ],
                'times' => 1,
                'duration' => 3600,
            ]
        );
        $url = $this->generateUrl('common_parse_qrcode', ['token' => $token['token']], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->generateUrl('common_qrcode', ['text' => $url], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    protected function setScrmData()
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $user = $this->getSCRMService()->setUserSCRMData($user);

        return $user;
    }

    protected function getScrmStudentBindUrl($assistant)
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());

        $bindUrl = $this->getSCRMService()->getWechatOauthLoginUrl($user, $this->generateUrl('scrm_user_bind_result', ['uuid' => $user['uuid'], 'assistantUuid' => $assistant['uuid']], UrlGeneratorInterface::ABSOLUTE_URL));

        return $bindUrl;
    }

    protected function convertFields($course)
    {
        $enableAudioStatus = $this->getCourseService()->isSupportEnableAudio($course['enableAudio']);
        $course['isAudioOn'] = $enableAudioStatus ? '1' : '0';
        $course['hasCertificate'] = $this->getCourseService()->hasCertificate($course['id']);
        unset($course['enableAudio']);
        $course = $this->getCourseService()->appendSpecInfo($course);

        return $course;
    }

    protected function joinCourseMemberByClassroomId($courseId, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $user = $this->getCurrentUser();

        $classroomMember = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);

        if (empty($classroomMember) || !in_array('student', $classroomMember['role'])) {
            return;
        }

        $info = [
            'joinedChannel' => $classroomMember['joinedChannel'],
            'deadline' => $classroomMember['deadline'],
        ];

        $this->getMemberService()->createMemberByClassroomJoined($courseId, $user['id'], $classroom['id'], $info);
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        if (isset($conditions['type']) && 'all' === $conditions['type']) {
            unset($conditions['type']);
        }

        $conditions['status'] = 'published';
        $conditions['courseSetStatus'] = 'published';
        $conditions['parentId'] = isset($conditions['parentId']) ? $conditions['parentId'] : 0;
        //过滤约排课
        $conditions['excludeTypes'] = ['reservation'];
        if (!empty($conditions['lastDays'])) {
            $timeRange = TimeMachine::getTimeRangeByDays($conditions['lastDays']);
            $conditions['outerStartTime'] = $timeRange['startTime'];
            $conditions['outerEndTime'] = $timeRange['endTime'];
        }

        if (!empty($conditions['excludeMultiClassCourses'])) {
            $multiClasses = $this->getMultiClassService()->findAllMultiClass();
            if (!empty($multiClasses)) {
                $conditions['excludeIds'] = ArrayToolkit::column($multiClasses, 'courseId');
            }
            unset($conditions['excludeMultiClassCourses']);
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $sort = $this->getSort($request);

        if ($this->isPluginInstalled('Vip') && isset($conditions['vipLevelId'])) {
            $vipCourseIds = $this->getVipCourseIdsByVipLevelId($conditions['vipLevelId']);
            $conditions['ids'] = empty($vipCourseIds) ? [-1] : $vipCourseIds;
            unset($conditions['vipLevelId']);
        }

        $courses = $this->getCourseService()->searchBySort($conditions, $sort, $offset, $limit);
        $total = $this->getCourseService()->countWithJoinCourseSet($conditions);

        $this->getOCUtil()->multiple($courses, ['creator', 'teacherIds']);
        $this->getOCUtil()->multiple($courses, ['courseSetId'], 'courseSet');

        $courses = $this->getCourseService()->appendHasCertificate($courses);
        $courses = $this->getCourseService()->appendSpecsInfo($courses);

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    protected function getVipCourseIdsByVipLevelId($vipLevelId)
    {
        if ('0' == $vipLevelId) {
            $levels = $this->getLevelService()->findEnabledLevels();
            $vipLevelIds = ArrayToolkit::column($levels, 'id');
        } else {
            if (empty($this->getLevelService()->getLevel($vipLevelId))) {
                return [];
            }
            $levels = $this->getLevelService()->findPrevEnabledLevels($vipLevelId);
            $vipLevelIds = array_merge(ArrayToolkit::column($levels, 'id'), [$vipLevelId]);
        }

        if (empty($vipLevelIds)) {
            return [];
        }

        $vipRights = $this->getVipRightService()->findVipRightsBySupplierCodeAndVipLevelIds('course', $vipLevelIds);

        return empty($vipRights) ? [] : ArrayToolkit::column($vipRights, 'uniqueCode');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    protected function getLevelService()
    {
        return $this->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->service('Assistant:AssistantStudentService');
    }

    protected function getVipRightService()
    {
        return $this->service('VipPlugin:Marketing:VipRightService');
    }

    /**
     * @return \Biz\SCRM\Service\SCRMService
     */
    protected function getSCRMService()
    {
        return $this->service('SCRM:SCRMService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}
