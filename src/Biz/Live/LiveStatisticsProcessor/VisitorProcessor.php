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
        foreach ($data as $user) {
            $userId = $this->getUserIdByNickName($user['nickName']);
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
            $totalLearnTime += ($user['leaveTime'] - $user['joinTime']);
        }

        return array(
            'totalLearnTime' => $totalLearnTime,
            'detail' => $result,
        );
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
