<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Taxonomy\TagGroupTagService;

class TagGroupTagServiceImpl extends BaseService implements TagGroupTagService
{
    private $allowConditions = array(
        'name', 'scope', 'tagNum', 'createdTime', 'updatedTime'
    );

    public function findTagsByGroupId($groupId)
    {
        return $this->getTagGroupTagDao()->findTagsByGroupId($groupId);
    }

    public function search($conditions, $order, $start, $limit)
    {
        $this->fieterConditions($conditions);

        return $this->getTagGroupTagDao()->search($conditions, $order, $start, $limit);
    }

    protected function fieterConditions($conditions)
    {
        return ArrayToolkit::parts($conditions, $this->allowConditions);
    }
        
    protected function getTagGroupTagDao()
    {
        $this->createDao('Taxonomy.TagGroupTag');
    }
}
