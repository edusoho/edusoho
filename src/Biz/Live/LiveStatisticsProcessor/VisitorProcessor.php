<?php

namespace Biz\Live\LiveStatisticsProcessor;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Topxia\Service\Common\ServiceKernel;

class VisitorProcessor extends AbstractLiveStatisticsProcessor
{
    private $teacherIds = array();

    public function handlerResult($result)
    {
        try {
            $this->checkResult($result);

            if (!empty($result['liveId'])) {
                $this->teacherIds = $this->getTeacherIdsByLiveId($result['liveId']);
            }

            $data = $this->handleData($result['data']);

            return $data;
        } catch (ServiceException $e) {
            throw $e;
        }
    }

    private function getTeacherIdsByLiveId($liveId)
    {
        $liveActivity = $this->getLiveActivityService()->search(array('liveId' => $liveId), array(), 0, 1);
        if (empty($liveActivity)) {
            return array();
        }

        $conditions = array(
            'mediaId' => $liveActivity[0]['id'],
            'mediaType' => 'live',
            'copyId' => 0,
        );
        $activity = $this->getActivityService()->search($conditions, array(), 0, 1);

        if (empty($activity)) {
            return array();
        }

        $teachers = $this->getCourseMemberService()->findCourseTeachers($activity[0]['fromCourseId']);

        return ArrayToolkit::column($teachers, 'userId');
    }

    private function handleData($data)
    {
        $result = array();
        $totalLearnTime = 0;
        try {
            foreach ($data as $user) {
                $user = $this->handleUser($user);
                if (empty($user)) {
                    continue;
                }
                $result = $this->handleUserResult($result, $user);
                $totalLearnTime += ($user['leaveTime'] - $user['joinTime']);
            }
        } catch (ServiceException $e) {
            $this->getLogService()->info('course', 'live', 'handle visitor data error: ', json_encode($data));

            return array(
                'totalLearnTime' => 0,
                'success' => 0,
                'detail' => array(),
            );
        }

        return array(
            'totalLearnTime' => $totalLearnTime,
            'success' => 1,
            'detail' => $result,
        );
    }

    private function handleUser($user)
    {
        $userId = $this->getUserIdByNickName($user['nickName']);
        if (empty($userId)) {
            throw new ServiceException('user not found');
        }

        $existUser = $this->getUserService()->getUser($userId);
        $nickname = empty($existUser['nickname']) ? $user['nickName'] : $existUser['nickname'];

        if (in_array($userId, $this->teacherIds)) {
            return array();
        }

        $user['userId'] = $userId;
        $user['nickname'] = $nickname;

        return $user;
    }

    private function handleUserResult($result, $user)
    {
        $userId = $user['userId'];
        if (empty($result[$userId])) {
            $result[$userId] = array(
                'userId' => $userId,
                'nickname' => $user['nickname'],
                'firstJoin' => $user['joinTime'],
                'lastLeave' => $user['leaveTime'],
                'learnTime' => $user['leaveTime'] - $user['joinTime'],
            );
        } else {
            $result[$userId] = array(
                'userId' => $userId,
                'nickname' => $user['nickname'],
                'firstJoin' => $result[$userId]['firstJoin'] > $user['joinTime'] ? $user['joinTime'] : $result[$userId]['firstJoin'],
                'lastLeave' => $result[$userId]['lastLeave'] > $user['leaveTime'] ? $result[$userId]['lastLeave'] : $user['leaveTime'],
                'learnTime' => $result[$userId]['learnTime'] + ($user['leaveTime'] - $user['joinTime']),
            );
        }

        return $result;
    }

    private function checkResult($result)
    {
        if (!isset($result['code']) || self::RESPONSE_CODE_SUCCESS != $result['code']) {
            $this->getLogService()->info('course', 'live', 'check code error: '.json_encode($result));
            throw new ServiceException('code is not success or not found');
        }

        if (!isset($result['data'])) {
            $this->getLogService()->info('course', 'live', 'check data error: '.json_encode($result));
            throw new ServiceException('data is not found');
        }

        return true;
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return ServiceKernel::instance()->createService('Activity:ActivityService');
    }

    /**
     * @return LiveActivityService
     */
    private function getLiveActivityService()
    {
        return ServiceKernel::instance()->createService('Activity:LiveActivityService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return ServiceKernel::instance()->createService('Course:MemberService');
    }
}
