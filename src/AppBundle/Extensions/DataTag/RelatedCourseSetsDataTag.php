<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Taxonomy\Service\TagService;

class RelatedCourseSetsDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $courseSetId = $arguments['courseSetId'];
        $count = $arguments['count'];

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if (empty($courseSet)) {
            return array();
        }
        $courseSets = $this->getCourseSetService()->findRelatedCourseSetsByCourseSetId($courseSetId, $count);

        return $courseSets;
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:TagService');
    }
}
