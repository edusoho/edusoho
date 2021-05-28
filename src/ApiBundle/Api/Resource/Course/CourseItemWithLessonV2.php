<?php

namespace ApiBundle\Api\Resource\Course;

class CourseItemWithLessonV2 extends CourseItemWithLesson
{
    protected function convertToTree($items)
    {
        return $this->container->get('api.util.item_helper')->convertToTreeV2($items);
    }
}
