<?php

namespace AppBundle\Common;

class ContentToolkit
{
    public static function extractImgs($thread)
    {
        $thread['imgs'] = [];
        if (empty($thread['content'])) {
            return $thread;
        }
        preg_match_all('/<img.*?src=["\'](.*?)["\'].*?>/i', $thread['content'], $matches);

        if (empty($matches)) {
            return $thread;
        }
        $thread['imgs'] = $matches[1];
        $thread['content'] = preg_replace('/\n*?(<p>)<img.*?src=["\'].*?["\'].*?>(<\/p>)?\n*?/i', '', $thread['content']);

        return $thread;
    }

    public static function insertionImgs($fields)
    {
        foreach ($fields['imgs'] as $img) {
            $fields['content'] .= "<p><img alt='' src='{$img}'></p>";
        }

        return $fields;
    }
}
