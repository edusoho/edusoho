<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class CourseChapter extends AbstractResource
{
    /**
     * post /api/course/{courseId}/chapter
     *
     * @see https://api.codeages.work/admin.do#/user/interface/detail?id=162091435782212000155&pageName=%E6%8E%A5%E5%8F%A3%E8%AF%A6%E6%83%85&dataType=interface&menu_a=menu-project&menu_b=menu_interface&projectName=edusoho-v3&projectId=157231946487107000002&moduleId=162090722776209000153&timestamp=1620960004905
     */
    public function add(ApiRequest $request, $courseId)
    {
        $chapterInfo = ArrayToolkit::parts($request->request->all(), ['type', 'title']);
        $chapterInfo['courseId'] = $courseId;

        return $this->getCourseService()->createChapter($chapterInfo);
    }

    /**
     * patch /api/course/{courseId}/chapter/{chapterId}
     *
     * @see https://api.codeages.work/admin.do#/user/interface/detail?id=162091435782212000155&pageName=%E6%8E%A5%E5%8F%A3%E8%AF%A6%E6%83%85&dataType=interface&menu_a=menu-project&menu_b=menu_interface&projectName=edusoho-v3&projectId=157231946487107000002&moduleId=162090722776209000153&timestamp=1620960004905
     */
    public function update(ApiRequest $request, $courseId, $chapterId)
    {
        $chapterInfo = ArrayToolkit::parts($request->request->all(), ['type', 'title']);

        return $this->getCourseService()->updateChapter($courseId, $chapterId, $chapterInfo);
    }

    /**
     * delete /api/course/{courseId}/chapter/{chapterId}
     *
     * @see https://api.codeages.work/admin.do#/user/interface/detail?id=162124624860712000017&pageName=%E6%8E%A5%E5%8F%A3%E8%AF%A6%E6%83%85&dataType=interface&menu_a=menu-project&menu_b=menu_interface&projectName=edusoho-v3&projectId=157231946487107000002&moduleId=162090722776209000153&timestamp=1621249979841
     */
    public function remove(ApiRequest $request, $courseId, $chapterId)
    {
        $this->getCourseService()->deleteChapter(
            $courseId,
            $chapterId
        );

        return ['success' => true];
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
