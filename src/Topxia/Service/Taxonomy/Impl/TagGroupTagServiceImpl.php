<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Taxonomy\TagGroupTagService;

class TagGroupTagServiceImpl extends BaseService implements TagGroupTagService
{
    public function findTagsByGroupId($groupId)
    {
        return $this->getTagGroupTagDao()->findTagsByGroupId($groupId);
    }

    protected function getTagGroupTagDao()
    {
        $this->createDao('Taxonomy.TagGroupTag');
    }
}
