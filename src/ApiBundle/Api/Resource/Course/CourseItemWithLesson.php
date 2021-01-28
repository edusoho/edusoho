<?php

namespace ApiBundle\Api\Resource\Course;

class CourseItemWithLesson extends CourseItem
{
    protected function convertToLeadingItems($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask = false)
    {
        return $this->container->get('api.util.item_helper')->convertToLeadingItemsV2($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask);
    }
}
