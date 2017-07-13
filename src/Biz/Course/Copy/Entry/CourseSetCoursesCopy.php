<?php

namespace Biz\Course\Copy\Entry;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseSetDao;
use Biz\Taxonomy\Dao\TagOwnerDao;
use Biz\Taxonomy\Service\TagService;
use Biz\Course\Copy\Chain\CourseSetCopy;

class CourseSetCoursesCopy extends AbstractEntityCopy
{
    protected function copyEntity($source, $config = array())
    {
        $newCourseSet = $this->doCopyCourseSet($source, $config);
        $this->doCopyTagOwners($newCourseSet);

    }

    protected function getFields()
    {

    }

    private function doCopyCourseSet($source, $config)
    {
        $courseSetCopy = new CourseSetCopy($this->biz);

        return $courseSetCopy->copy($source, $config);
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

    /**
     * @return CourseSetDao
     */
    private function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }

    /**
     * @return TagOwnerDao
     */
    private function getTagOwnerDao()
    {
        return $this->biz->dao('Taxonomy:TagOwnerDao');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->biz->service('Taxonomy:TagService');
    }
}