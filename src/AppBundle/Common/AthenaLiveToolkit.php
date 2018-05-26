<?php

namespace AppBundle\Common;

class AthenaLiveToolkit
{
    public static function generateCallback($baseUrl, $token, $courseId)
    {
        //members.fetch的callback云平台这块已删除
        $memberUrl = "{$baseUrl}/callback/athenaLive?ac=members.fetch&token={$token}&courseId={$courseId}";
        $mediaUrl = "{$baseUrl}/callback/athenaLive?ac=files.fetch&token={$token}&courseId={$courseId}";
        $uploadUrl = "{$baseUrl}/callback/athenaLive?ac=files.create&token={$token}&courseId={$courseId}";

        return array(
            array('type' => 'member', 'url' => $memberUrl),
            array('type' => 'media', 'url' => $mediaUrl),
            array('type' => 'upload', 'url' => $uploadUrl),
        );
    }
}
