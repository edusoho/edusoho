<?php

namespace Biz\Marker\Service;

interface ReportService
{
    /**
     * 任务下的弹题统计报表
     *
     * @param $courseId
     * @param $taskId
     *
     * @return mixed
     */
    public function statTaskQuestionMarker($courseId, $taskId);

    /**
     * 单个弹题的统计分析
     *
     * @param $courseId
     * @param $taskId
     * @param $questionMarkerId
     *
     * @return mixed
     */
    public function analysisQuestionMarker($courseId, $taskId, $questionMarkerId);
}
