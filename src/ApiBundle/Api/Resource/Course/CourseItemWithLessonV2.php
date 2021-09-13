<?php

namespace ApiBundle\Api\Resource\Course;

/**
 * 大班课使用
 */
class CourseItemWithLessonV2 extends CourseItemWithLesson
{
    protected function convertToTree($items)
    {
        return $this->container->get('api.util.item_helper')->convertToTreeV2($items);
    }
}
