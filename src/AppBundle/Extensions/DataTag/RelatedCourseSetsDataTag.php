<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Taxonomy\Service\TagService;

class RelatedCourseSetsDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        //todo find courses by tags
        $courseSetId = $arguments['courseSetId'];
        $count = $arguments['count'];

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $courseSetTags = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType($courseSet['tags'], 'course-set');

        $courseSetTags = array_filter($courseSetTags, function ($value) use ($courseSetId) {
            return $value['ownerId'] != $courseSetId;
        });
        //按标签相关度排序
        $courseSetIds = array();
        foreach ($courseSetTags as $tag) {
            if (empty($courseSetIds[$tag['ownerId']])) {
                $courseSetIds[$tag['ownerId']] = 1;
            } else {
                $courseSetIds[$tag['ownerId']] += 1;
            }
        }
        //对值按从大到小排序
        arsort($courseSetIds);
        $courseSetIds = array_keys($courseSetIds);
        if (count($courseSetIds) > $count) {
            $courseSetIds = array_slice($courseSetIds, 0, $count);
        }

        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        uksort($courseSets, function ($c1, $c2) use ($courseSetIds) {
            return array_search($c1, $courseSetIds) > array_search($c2, $courseSetIds);
        });

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
