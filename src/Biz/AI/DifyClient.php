<?php

namespace Biz\AI;

class DifyClient
{
    public function request($apiKey, $inputs)
    {
        $body = json_encode([
            'inputs' => $inputs,
            'response_mode' => 'streaming',
            'user' => 'test',
        ]);
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer $apiKey",
        ];
        $response = '';
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
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$response) {
            echo $data;
            $response .= $data;
            return strlen($data);
        });

        curl_exec($ch);

        curl_close($ch);

        return $response;
    }
}
