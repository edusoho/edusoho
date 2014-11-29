<?php
namespace Topxia\Service\Essay\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Essay\EssayService;
use Topxia\Common\ArrayToolkit;

class EssayServiceImpl extends BaseService  implements EssayService
{
    public function getEssay ($id)
    {
        return $this->getEssayDao()->getEssay($id);
    }

    public function searchEssays (array $conditions, array $oderBy, $start, $limit)
    {
        return $this->getEssayDao()->searchEssays($conditions, $oderBy, $start, $limit);
    }

    public function searchEssaysCount(array $conditions)
    {
        return $this->getEssayDao()->searchEssaysCount($conditions);
    }

    public function createEssay(array $essay)
    {
        if (empty($essay)) {
            $this->createServiceException("内容为空，创建文章失败！");
        }

        $essay = $this->filterEssayFields($essay);

        return $this->getEssayDao()->addEssay($essay);
    }

    public function updateEssay($id,$essay)
    {
        if (empty($essay)) {
            $this->createServiceException("内容为空，编辑文章失败！");
        }

        $essay = $this->filterEssayFields($essay,'update');

        return $this->getEssayDao()->updateEssay($id,$essay);
    }

    public function deleteEssay ($id)
    {
        $this->getEssayDao()->deleteEssay($id);
        return true;
    }

    public function deleteEssaysByIds($ids)
    {
        if (count($ids) == 1) {
            $this->getEssayDao()->deleteEssay($ids[0]);
        } else {
            foreach ($ids as $id) {
                $this->getEssayDao()->deleteEssay($id);
            }
        }
        return true;
    }

    public function publishEssay($id)
    {
        $this->getEssayDao()->updateEssay($id, $fields = array('status' => 'published','publishedTime'=>time()));
        $this->getLogService()->info('Essay', 'publish', "文章#{$id}发布");
    }

    public function unpublishEssay($id)
    {
        $this->getEssayDao()->updateEssay($id, $fields = array('status' => 'unpublished'));
        $this->getLogService()->info('Essay', 'unpublish', "文章#{$id}发布");
    }

    private function filterEssayFields($essay,$mode='add')
    {
        $essay = ArrayToolkit::parts($essay,array('title','description','source','categoryId'));
        if ($mode == 'add') {
            $essay['createdTime'] = time();
            $essay['userId'] = $this->getCurrentUser()->id;
        } else {
            $essay['updatedTime'] = time();
        }
        return $essay;
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

    private function getEssayDao()
    {
        return $this->createDao('Essay.EssayDao');
    }
}