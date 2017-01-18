<?php

namespace Biz\Course\Copy\Impl;

class ActivityTestpaperCopy extends TestpaperCopy
{
    /**
     * 复制链说明：
     * Testpaper 试卷/作业/练习
     * - TestpaperItem 题目列表
     *   - Question 题目内容
     * @param $biz
     * @param $type
     */
    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    /*
     * - $source = $activity
     * - $config:
     * */
    protected function _copy($source, $config = array())
    {
        $this->addError('ActivityTestpaperCopy', 'copy source:'.json_encode($source));
        return $this->doCopyTestpaper($source);
    }

    public function doCopyTestpaper($activity)
    {
        $newTestpaper                = $this->baseCopyTestpaper($testpaper);
        $newTestpaper['courseSetId'] = $activity['fromCourseSetId'];
        $newTestpaper['courseId']    = $activity['fromCourseId'];

        $newTestpaper = $this->getTestpaperDao()->create($newTestpaper);
        $this->doCopyTestpaperItems($testpaper, $newTestpaper);

        return $newTestpaper;
    }
}
