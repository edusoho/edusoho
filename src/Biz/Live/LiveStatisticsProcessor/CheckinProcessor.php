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
        $result = array();
        $users = $data['users'];
        foreach ($users as $user) {
            $userId = $this->getUserIdByNickName($user['nickName']);
            $user['userId'] = $userId;
            $result[] = $user;
        }

        return array(
            'time' => $data['time'],
            'data' => $result,
            'detail' => $data
        );
    }

    private function getUserIdByNickName($nickname)
    {
        $list = explode('_', $nickname);
        if (count($list) == 2) {
            return $list[1];
        }

        return 0;
    }

    private function checkResult($result)
    {
        if (!isset($result['code']) || self::RESPONSE_CODE_SUCCESS != $result['code']) {
            $this->getLogService()->info('live', 'check code error: ' . json_encode($result));
            throw new ServiceException('code is not success or not found');
        }

        if (!isset($result['data'])) {
            $this->getLogService()->info('live', 'check data error: ' . json_encode($result));
            throw new ServiceException('data is not found');
        }

        return true;
    }
}