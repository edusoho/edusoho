<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;

class CourseItem extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        $items = $this->convertToLeadingItems(
            $this->getCourseService()->findCourseItems($courseId),
            $course,
            $request->getHttpRequest()->isSecure(),
            $request->query->get('fetchSubtitlesUrls', 0),
            $request->query->get('onlyPublished', 0)
        );

        $request->query->has('format') ? $format = $request->query->get('format') : $format = 0;

        if ($format) {
            $filter = new CourseItemWithLessonFilter();
            $filter->filters($items);
            $items = $this->convertToTree($items);
        }

        return $items;
    }

    protected function convertToLeadingItems($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask = false)
    {
        return $this->container->get('api.util.item_helper')->convertToLeadingItemsV1($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask);
    }

    protected function convertToTree($items)
    {
        return $this->container->get('api.util.item_helper')->convertToTree($items);
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getSubtitleService()
    {
        return $this->service('Subtitle:SubtitleService');
    }
}
