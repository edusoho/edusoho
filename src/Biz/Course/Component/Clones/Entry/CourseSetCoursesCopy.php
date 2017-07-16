<?php

namespace Biz\Course\Component\Clones\Entry;

use Biz\Course\Component\Clones\AbstractClone;
use Biz\Course\Component\Clones\Chain\CourseSetMaterialClone;
use Biz\Taxonomy\Service\TagService;
use Biz\Course\Component\Clones\Chain\CourseSetCopy;

class CourseSetCoursesCopy extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        $newCourseSet = $this->doCopyCourseSet($source,$options);
        $this->doCopyTagOwners($newCourseSet);
        $options['newCourseSet'] = $newCourseSet;
        $cloneCourseSetMaterial = new CourseSetMaterialClone($this->biz);
        $cloneCourseSetMaterial->clones($source,$options);
    }

    private function doCopyCourseSet($source, $options)
    {
        $courseSetCopy = new CourseSetCopy($this->biz);

        return $courseSetCopy->clones($source, $options);
    }

    public function doCopyTagOwners($newCourseSet)
    {
        if (empty($newCourseSet['tags'])) {
            return false;
        }

        $newTagOwners = array();
        foreach ($newCourseSet['tags'] as $tag) {
            $tagOwner = array(
                'ownerType' => 'course-set',
                'ownerId' => $newCourseSet['id'],
                'tagId' => $tag,
                'userId' => $newCourseSet['creator'],
            );

            $newTagOwners[] = $tagOwner;
        }

        $this->getTagService()->batchCreateTagOwner($newTagOwners);

        return true;
    }

    protected function getFields()
    {
        // TODO: Implement getFields() method.
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->biz->service('Taxonomy:TagService');
    }
}
