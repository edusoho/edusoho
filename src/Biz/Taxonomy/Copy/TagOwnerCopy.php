<?php

namespace Biz\Taxonomy\Copy;

use Biz\AbstractCopy;
use Biz\Taxonomy\Dao\TagDao;
use Codeages\Biz\Framework\Context\Biz;

class TagOwnerCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
        return;
    }

    public function doCopy($source, $options)
    {
        $newCourseSet = $options['newCourseSet'];

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

        if (empty($newTagOwners)) {
            return false;
        }

        $this->getTagOwnerDao()->batchCreate($newTagOwners);

        return true;
    }

    public function __construct(Biz $biz, $copyChain)
    {
        parent::__construct($biz, $copyChain);
    }

    protected function getFields()
    {
        return array();
    }

    /**
     * @return TagDao
     */
    protected function getTagOwnerDao()
    {
        return $this->biz->dao('Taxonomy:TagOwnerDao');
    }
}
