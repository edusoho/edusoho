<?php

namespace Biz\Live\LiveStatisticsProcessor;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getTeacherIdsByLiveId($liveId)
    {
        $liveActivity = $this->getLiveActivityService()->getByLiveId($liveId);
        if (empty($liveActivity)) {
            return array();
        }

        $activity = $this->getActivityService()->getByMediaIdAndMediaTypeAndCopyId($liveActivity['id'], 'live', 0);

        if (empty($activity)) {
            return array();
        }

        $teachers = $this->getCourseMemberService()->findCourseTeachers($activity['fromCourseId']);

        return ArrayToolkit::column($teachers, 'userId');
    }

    private function handleData($data)
    {
        if (empty($data)) {
            return array('success' => 1);
        }

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
        $userId = $this->splitUserIdFromNickName($user['nickName']);
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
        $user['joinTime'] /= 1000;
        $user['leaveTime'] /= 1000;

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

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return LiveActivityService
     */
    private function getLiveActivityService()
    {
        return $this->biz->service('Activity:LiveActivityService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
