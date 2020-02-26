<?php

namespace Biz\Live\LiveStatisticsProcessor;

use Codeages\Biz\Framework\Service\Exception\ServiceException;

class HistoryProcessor extends AbstractLiveStatisticsProcessor
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
        foreach ($data as $user) {
            $user['userId'] = $this->getUserIdByNickName($user['nickName']);
            $result[$user['userId']] = $user;
        }

        return array(
            'data' => $result,
            'detail' => $data
        );
    }

    private function checkResult($result)
    {
        if (!isset($result['code']) || self::RESPONSE_CODE_SUCCESS != $result['code']) {
            $this->getLogService()->info('course','live', 'check code error: ' . json_encode($result));
            throw new ServiceException('code is not success or not found');
        }

        if (!isset($result['data'])) {
            $this->getLogService()->info('course','live', 'check data error: ' . json_encode($result));
            throw new ServiceException('data is not found');
        }

        return true;
    }
}