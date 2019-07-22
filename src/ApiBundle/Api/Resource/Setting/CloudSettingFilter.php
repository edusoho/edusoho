<?php

namespace ApiBundle\Api\Resource\Setting;

use ApiBundle\Api\Resource\Filter;

class CloudSettingFilter extends Filter
{
    protected $publicFields = array(
        'status', 'sms'
    );

    protected function publicFields(&$data)
    {
        if (isset($data['sms'])) {
            $smsFilter = new CloudSmsFilter();
            $smsFilter->filter($data['sms']);
            // 每个子功能都要过滤状态，统一规范
            $this->filterStatus($data['sms'], $data['status']);
        }
        // 前端不用去感知『云平台状态』是否可用
        unset($data['status']);
    }

    private function filterStatus(&$data, $cloudStatus)
    {
        foreach ($data as $key => $value)
        {
            if ($cloudStatus) {
                // 将所有是非条件转为 0 和 1：统一规范、益于前端强类型语言、益于拓展第三种状态
                switch ($value) {
                    case 'on':
                        $data[$key] = 1;
                        break;
                    case 'off':
                        $data[$key] = 0;
                        break;
                    default:
                        break;
                }
            } else {
                // 如果云平台状态不可用（未开通教育云、用户被教育云封禁、未填写正确的accessKey），则所有子功能不可用
                // 接口需要返回 XX 功能可不可用，而不是让前端去判断『云平台状态』可不可用
                $data[$key] = 0;
            }
        }
    }
}
