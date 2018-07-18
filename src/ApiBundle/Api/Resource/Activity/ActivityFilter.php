<?php

namespace ApiBundle\Api\Resource\Activity;

use ApiBundle\Api\Resource\Filter;

class ActivityFilter extends Filter
{
    protected $publicFields = array(
        'id', 'remark', 'ext', 'mediaType', 'mediaId', 'startTime',
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['ext']) && !empty($data['ext']['replayStatus'])) {
            $data['replayStatus'] = $data['ext']['replayStatus'];
        }

        if (!empty($data['ext']) && !empty($data['ext']['finishType'])) {
            $data['finishType'] = $data['ext']['finishType'];
        }

        if (!empty($data['ext']) && !empty($data['ext']['finishDetail'])) {
            $data['finishDetail'] = $data['ext']['finishDetail'];
        }

        if (!empty($data['ext']) && !empty($data['ext']['finishCondition'])) {
            $data['finishDetail'] = $data['ext']['finishCondition']['finishScore'];
            $data['finishType'] = $data['ext']['finishCondition']['type'];
        }

        if (!empty($data['ext']) && !empty($data['ext']['file'])) {
            $data['mediaStorage'] = $data['ext']['file']['storage'];
        }

        //testpaper module
        if ('testpaper' == $data['mediaType']) {
            if (!empty($data['ext'])) {
                $data['testpaperInfo']['testMode'] = $data['ext']['testMode'];
                $data['testpaperInfo']['limitTime'] = $data['ext']['limitedTime'];
                $data['testpaperInfo']['redoInterval'] = $data['ext']['redoInterval'] * 60; //分钟
                $data['testpaperInfo']['doTimes'] = $data['ext']['doTimes'];
                $data['testpaperInfo']['startTime'] = !empty($data['startTime']) ? date('c', $data['startTime']) : null;
            }
        }

        unset($data['ext']);
        unset($data['mediaType']);
    }
}
