<?php 
namespace Topxia\Service\Taxonomy;

use Topxia\Service\Common\ServiceKernel;

class TagOwnerManager
{
    private $ownerType;
    private $ownerId;
    private $tagIds;
    private $userId;

    public function __construct($ownerType, $ownerId, $tagIds = array(), $userId = null)
    {
        $this->ownerType = $ownerType;
        $this->ownerId   = $ownerId;
        $this->tagIds    = $tagIds;
        $this->userId    = $userId;
    }

    public function create()
    {
        foreach ($this->tagIds as $tagId) {
            $this->getTagService()->addTagOwnerRelation(array(
                'ownerType'   => $this->ownerType,
                'ownerId'     => $this->ownerId,
                'tagId'       => $tagId,
                'userId'      => $this->userId,
                'createdTime' => time()
            ));
        }
    }

    public function update()
    {
        $this->delete();

        $this->create();    
    }

    public function delete()
    {
        $this->getTagService()->deleteTagOwnerRelationsByOwner(array('ownerType' => $this->ownerType, 'ownerId' => $this->ownerId));
    }

    protected function getTagService()
    {
        return ServiceKernel::instance()->createService('Taxonomy.TagService');
    }
}
