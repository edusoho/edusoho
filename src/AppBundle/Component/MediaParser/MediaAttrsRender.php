<?php

namespace AppBundle\Component\MediaParser;

use AppBundle\Common\TimeMachine;

class MediaAttrsRender
{
    private static $mockedTime = 0;

    public static function render($media)
    {
        $attrs = array();
        if (empty($media) || empty($media['id']) || empty($media['uuid'])) {
            return json_encode($attrs);
        }

        if (false !== stripos($media['uuid'], 'YoukuVideo')) {
            $attrs['swf_url'] = $media['swf_url'];
            $attrs['apple_url'] = "http://v.youku.com/player/getM3U8/vid/{$media['id']}/ts/".TimeMachine::time().'/v.m3u8';
        } elseif (false !== stripos($media['uuid'], 'QQVideo')) {
            $attrs['swf_url'] = $media['swf_url'];
            $attrs['mp4_url'] = "http://video.store.qq.com/{$media['id']}.mp4";
        } else {
            if (!empty($media['swf_url'])) {
                $attrs['swf_url'] = $media['swf_url'];
            }

            if (!empty($media['mp4_url'])) {
                $attrs['mp4_url'] = $media['mp4_url'];
            }

            if (!empty($media['apple_url'])) {
                $attrs['apple_url'] = $media['apple_url'];
            }
        }

        return json_encode($attrs);
    }
}
