<?php

namespace Biz\Live\LiveStatisticsProcessor;

use Codeages\Biz\Framework\Service\Exception\ServiceException;

class VisitorProcessor extends AbstractLiveStatisticsProcessor
{
    public function handlerResult($result)
    {
        try {
            $this->checkResult($result);

            $data = $this->handleData($result['data']);

            return $data;
        } catch (ServiceException $e) {
            throw $e;
        }
    }

    private function handleData($data)
    {
        $result = array();
        $totalLearnTime = 0;
        try {
            foreach ($data as $user) {
                $result = $this->handleUser($result, $user);
                $totalLearnTime += ($user['leaveTime'] - $user['joinTime']);
            }
        } catch (ServiceException $e) {
            $this->getLogService()->info('course', 'live', 'handle visitor data error: ',json_encode($data));

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

    private function handleUser($result, $user)
    {
        $userId = $this->getUserIdByNickName($user['nickName']);
        if (empty($userId)) {
            throw new ServiceException('user not found');
        }
        if (empty($result[$userId])) {
            $result[$userId] = array(
                'firstJoin' => $user['joinTime'],
                'lastLeave' => $user['leaveTime'],
                'learnTime' => $user['leaveTime'] - $user['joinTime'],
            );
        } else {
            $result[$userId] = array(
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
}
