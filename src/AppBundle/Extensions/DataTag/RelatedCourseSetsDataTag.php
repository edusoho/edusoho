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
        arsort($courseSetIds);
        
        $courseSetIds = array_keys($courseSetIds);

        $courseSets = $this->getCourseSetService()->searchCourseSets(array('ids' => $courseSetIds, 'status' =>'published', 'parentId' => 0), array(), 0, PHP_INT_MAX);

        uksort($courseSets, function ($c1, $c2) use ($courseSetIds) {
            return array_search($c1, $courseSetIds) > array_search($c2, $courseSetIds);
        });

        if (count($courseSets) > $count) {
            return array_slice($courseSets, 0, $count);
        }

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
