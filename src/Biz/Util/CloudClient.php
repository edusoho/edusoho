<?php

namespace Biz\Util;

interface CloudClient
{
    public function getVideoConvertCommands();

    public function getAudioConvertCommands();

    public function getPPTConvertCommands();

    public function convertPPT($key, $notifyUrl = null);

    public function getAudioInfo($key);

    public function removeFile($key);

    public function moveFiles(array $files);

    public function getFileUrl($key, $targetId, $targetType);

    public function getBills();

    public function convertVideo($key, $commands, $notifyUrl);

    public function deleteFiles(array $keys, array $prefixs = array());

    public function getMediaInfo($key, $mediaType);

    public function generateFileUrl($key, $duration);

}
