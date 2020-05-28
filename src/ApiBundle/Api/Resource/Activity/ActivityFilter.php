<?php

namespace ApiBundle\Api\Resource\Activity;

use ApiBundle\Api\Resource\Filter;

class ActivityFilter extends Filter
{
    protected $publicFields = [
        'id', 'remark', 'ext', 'mediaType', 'mediaId', 'startTime', 'content', 'title', 'finishData', 'finishType', 'finishCondition',
    ];

    protected function publicFields(&$data)
    {
        if (!empty($data['ext']) && !empty($data['ext']['replayStatus'])) {
            $data['replayStatus'] = $data['ext']['replayStatus'];
        }

        if (!empty($data['ext']) && !empty($data['ext']['liveProvider'])) {
            $data['liveProvider'] = $data['ext']['liveProvider'];
        }

        if (!empty($data['finishData'])) {
            $data['finishDetail'] = $data['finishData'];
        }

        if (!empty($data['ext']) && !empty($data['ext']['file'])) {
            $data['mediaStorage'] = $data['ext']['file']['storage'];
        }

        if (!empty($data['ext']) && !empty($data['ext']['finishCondition'])) {
            $data['finishDetail'] = (string) $data['ext']['finishCondition']['finishScore'];
        }

        //testpaper module
        if ('testpaper' == $data['mediaType']) {
            if (!empty($data['ext'])) {
                $data['testpaperInfo']['testpaperId'] = $data['ext']['mediaId'];
                $data['testpaperInfo']['testMode'] = $data['ext']['testMode'];
                $data['testpaperInfo']['limitTime'] = $data['ext']['limitedTime'];
                $data['testpaperInfo']['redoInterval'] = $data['ext']['redoInterval']; //分钟
                $data['testpaperInfo']['doTimes'] = $data['ext']['doTimes'];
                $data['testpaperInfo']['startTime'] = !empty($data['startTime']) ? $data['startTime'] : null;
            }
        }

        // 老数据 以下三种类型不返回 完成条件
        $finishConditionWhiteList = ['audio', 'download', 'live'];
        if (in_array($data['mediaType'], $finishConditionWhiteList)) {
            unset($data['finishDetail']);
            unset($data['finishType']);
        }
        // 老数据文档
        if (in_array($data['mediaType'], ['text', 'doc'])) {
            unset($data['finishType']);
        }

        unset($data['ext']);
        unset($data['mediaType']);
    }
}
