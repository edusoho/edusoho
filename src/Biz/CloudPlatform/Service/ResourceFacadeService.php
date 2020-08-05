<?php

namespace Biz\CloudPlatform\Service;

interface ResourceFacadeService
{
    /**
     * 资源播放
     */
    public function makePlayToken($file, $lifetime = 600, $payload = []);

    public function agentInWhiteList($userAgent);

    public function getFrontPlaySDKPathByType($type);

    /**
     * 资源上传
     *
     * @param $file
     */
    public function startUpload($file);

    /**
     * 资源续传
     *
     * @param $params
     *        name 文件名
     *        extno file.id
     *        resumeNo 断点续传需要的参数，globalId
     */
    public function resumeUpload($params, $file);

    public function finishUpload($globalId);

    public function getResource($globalId);
}
