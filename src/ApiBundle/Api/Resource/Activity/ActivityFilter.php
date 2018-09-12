<?php

namespace ApiBundle\Api\Resource\Activity;

use ApiBundle\Api\Resource\Filter;

class ActivityFilter extends Filter
{
    protected $publicFields = array(
        'id', 'remark', 'ext', 'mediaType', 'mediaId', 'startTime', 'content', 'title',
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['ext']) && !empty($data['ext']['replayStatus'])) {
            $data['replayStatus'] = $data['ext']['replayStatus'];
        }

        if (!empty($data['finishDate'])) {
            $data['finishDetail'] = $data['finishDate'];
        }

        if (!empty($data['ext']) && !empty($data['ext']['file'])) {
            $data['mediaStorage'] = $data['ext']['file']['storage'];
        }

        //testpaper module
        if ('testpaper' == $data['mediaType']) {
            if (!empty($data['ext'])) {
                if (!empty($data['ext']['finishCondition']['finishScore'])) {
                    $data['finishDetail'] = $data['ext']['finishCondition']['finishScore'];
                } else {
                    $data['finishDetail'] = 0;
                }

                $data['testpaperInfo']['testMode'] = $data['ext']['testMode'];
                $data['testpaperInfo']['limitTime'] = $data['ext']['limitedTime'];
                $data['testpaperInfo']['redoInterval'] = $data['ext']['redoInterval'] * 60; //分钟
                $data['testpaperInfo']['doTimes'] = $data['ext']['doTimes'];
                $data['testpaperInfo']['startTime'] = !empty($data['startTime']) ? $data['startTime'] : null;
            }
        }

        unset($data['ext']);
        unset($data['mediaType']);
    }
}
