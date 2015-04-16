<?php
namespace Topxia\Service\CloudPlatform\Client;

use Topxia\System;

class EduSohoOpenClient
{

    public function getArticle($name)
    {
        $userAgent = 'Open Edusoho App Client 1.0';
        $connectTimeout = 10;
        $timeout = 10;
         if ($name == 'license') {
            $url = "http://open.edusoho.com/api/v1/context/articles";
        }else{
            $url = "http://open.edusoho.com/api/v1/context/notice";
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $url );
        $notices = curl_exec($curl);
        curl_close($curl);

        return $notices;
    }

}