<?php
namespace Topxia\Common;

class CurlToolkit
{
	public static function postRequest($url, $params, $conditions = array())
    {
        $curl = curl_init();
        $conditions['userAgent'] = isset($conditions['userAgent']) ?:'Topxia OAuth Client 1.0';
        $conditions['connectTimeout'] = isset($conditions['connectTimeout']) ?:10;
        $conditions['timeout'] = isset($conditions['timeout']) ?:10;

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_USERAGENT, $conditions['userAgent']);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $conditions['connectTimeout']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $conditions['timeout']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, $url );

        curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

}