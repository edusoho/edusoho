<?php

namespace Biz\Live\LiveStatisticsProcessor;

use Codeages\Biz\Framework\Service\Exception\ServiceException;

class CheckinProcessor extends AbstractLiveStatisticsProcessor
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
        if (empty($data)) {
            return array();
        }

        try {
            $users = $data[0]['users'];
            foreach ($users as &$user) {
                $user = $this->handleUser($user);
            }
        } catch (ServiceException $e) {
            $this->getLogService()->info('course', 'live', 'handle checkin data error: ',json_encode($data));

            return array(
                'time' => 0,
                'success' => 0,
                'detail' => array(),
            );
        }

        return array(
            'time' => $data[0]['time'],
            'success' => 1,
            'detail' => $users,
        );
    }

    private function handleUser($user)
    {
        $userId = $this->getUserIdByNickName($user['nickName']);
        if (empty($userId)) {
            throw new ServiceException('user not found');
        }
        $user['userId'] = $userId;
        return $user;
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
