<?php

namespace Biz\Face\Service;

interface FaceService
{
    const FACE_STATUS_FAIL = 'failed';
    const FACE_STATUS_SUCCESS = 'successed';
    const FACE_FIAL_TIMES = 5;
    const FACE_FIAL_TIME_INTERVAL = 300;

    const face_type = 'default';

    public function getAiFaceSdk();

    public function createFaceLog($log);

    public function countFaceLog($conditions);
}
