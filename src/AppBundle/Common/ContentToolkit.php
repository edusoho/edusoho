<?php

namespace AppBundle\Common;

class ContentToolkit
{
    public static function extractImgs($content)
    {
        preg_match_all('/<img.*?src=["\'](.*?)["\'].*?>/i', $content, $matches);

        return $matches[1] ?? [];
    }

    public static function filterImgs($content)
    {
        return preg_replace('/\n*(<p>)<img.*?src=["\'].*?["\'].*?>(<\/p>)?\n*/i', '', $content);
    }

    public static function appendImgs($content, $imgs)
    {
        foreach ($imgs as $img) {
            $content .= "<p><img alt='' src='{$img}'></p>";
        }

        return $content;
    }
}
