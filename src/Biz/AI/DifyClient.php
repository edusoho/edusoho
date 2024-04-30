<?php

namespace Biz\AI;

class DifyClient
{
    public function request($query)
    {
        $body = json_encode([
            'inputs' => ['query' => $query],
            'response_mode' => 'streaming',
            'user' => 'test',
        ]);
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer app-QuF1x9FZSBQTJvYNsj9zTmBw',
        ];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://dify.edusoho.cn/v1/completion-messages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
            echo $data;
            return strlen($data);
        });

        curl_exec($ch);

//        if (curl_errno($ch)) {
//            file_put_contents('./log/curl.error.log', curl_error($ch).PHP_EOL.PHP_EOL, FILE_APPEND);
//        }

        curl_close($ch);
    }
}
