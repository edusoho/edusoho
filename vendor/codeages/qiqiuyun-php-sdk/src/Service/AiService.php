<?php

namespace QiQiuYun\SDK\Service;

/**
 * AI服务
 */
class AiService extends BaseService
{
    protected $host = 'ai-service.qiqiuyun.net';

    /**
     * 创建人脸识别会话
     *
     * @see http://docs.qiqiuyun.com/v2/ai-face.html
     *
     * @param $userId int 用户id
     * @param $userName string 用户名
     * @param $type string 会话类型 register:注册 compare:对比
     *
     * @return array 会话信息
     */
    public function createFaceSession($userId, $userName, $type)
    {
        return $this->request('POST', '/face/sessions', array('user_id' => $userId, 'username' => $userName, 'type' => $type));
    }

    /**
     * 获取人脸识别会话信息
     *
     * @see http://docs.qiqiuyun.com/v2/ai-face.html
     *
     * @param string $sessionId 会话id
     *
     * @return array 会话信息
     */
    public function getFaceSession($sessionId)
    {
        return $this->request('GET', "/face/sessions/{$sessionId}");
    }

    /**
     * 完成人脸上传
     *
     * @see http://docs.qiqiuyun.com/v2/ai-face.html
     *
     * @param string $sessionId    会话id
     * @param int    $responseCode 上传后返回的http状态码，由存储供应商返回
     * @param string $responseBody 上传结果，由存储供应商返回
     *
     * @return array
     */
    public function finishFaceUpload($sessionId, $responseCode, $responseBody)
    {
        return $this->request('POST', "/face/sessions/{$sessionId}/finish_upload", array('response_code' => $responseCode, 'response_body' => $responseBody));
    }
}
