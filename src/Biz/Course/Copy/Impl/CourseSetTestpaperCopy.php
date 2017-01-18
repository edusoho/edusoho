<?php

namespace Biz\Course\Copy\Impl;

class CourseSetTestpaperCopy extends TestpaperCopy
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
     * type='course-set'
     * - $source = originalCourse
     * - $config : newCourseSet
     *
     * type='course'
     * - $source = originalCourse
     * - $config : newCourse
     * */
    protected function _copy($source, $config = array())
    {
        $that->addError('CourseSetTestpaperCopy', 'copy source:'.json_encode($source));
        return $this->doCopyTestpaper($config['newCourseSet'], $source['courseSetId']);
    }

    private function doCopyTestpaper($newCourseSet, $courseSetId)
    {
        $testpapers = $this->getTestpaperDao()->search(array('courseSetId' => $courseSetId), array(), 0, PHP_INT_MAX);
        if (empty($testpapers)) {
            return array();
        }
        $newTestpapers = array();
        foreach ($testpapers as $testpaper) {
            if ($testpaper['courseId'] > 0) {
                continue;
            }

            $newTestpaper                = $this->baseCopyTestpaper($testpaper);
            $newTestpaper['courseSetId'] = $newCourseSet['id'];
            $newTestpaper['courseId']    = 0;

            $newTestpaper = $this->getTestpaperDao()->create($newTestpaper);
            $this->doCopyTestpaperItems($testpaper, $newTestpaper);

            $newTestpapers[] = $newTestpaper;
        }

        return $newTestpapers;
    }
}
