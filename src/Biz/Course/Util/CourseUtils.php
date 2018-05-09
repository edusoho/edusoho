<?php

namespace Biz\Course\Util;

class CourseUtils
{
    public static function getCourseUrl($biz, $baseUrl, $params)
    {
        $baseInfos = explode('.html.twig', $baseUrl);
        $typeUrl = $baseInfos[0].'_'.$params['type'].'.html.twig';
        $typePath = $biz['kernel.root_dir'].'/app/Resources/views/'.$typeUrl;
        $basePath = $biz['kernel.root_dir'].'/app/Resources/views/'.$baseUrl;

        if ($biz['file_toolkit']->file_exists($typePath)) {
            return $typeUrl;
        }

        return $baseUrl;
    }
}
