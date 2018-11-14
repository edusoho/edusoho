<?php

namespace AppBundle\Common;

class ESLiveToolkit
{
    public static function generateCallback($baseUrl, $token, $courseId)
    {
        //members.fetch的callback云平台这块已删除
        $memberUrl = "{$baseUrl}/callback/ESLive?ac=members.fetch&dataType={$dataType}&source={$source}&mediaType={$mediaType}&courseId={$courseId}";

        return array(
            array('type' => 'member', 'url' => $memberUrl),
            array('type' => 'media', 'url' => $mediaUrl),
            array('type' => 'upload', 'url' => $uploadUrl),
        );
    }
}
