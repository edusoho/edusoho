<?php
namespace Custom\Service\TopLink\Impl;

use Custom\Service\TopLink\TopLinkService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class TopLinkServiceImpl extends BaseService implements TopLinkService
{
    public function getTopLink($id)
    {
        if(empty($id)){
            return null;
        }
        return $this->getTopLinkDao()->getTopLink($id);
    }

    public function searchTopLinks(array $conditions, array $orderBy, $start, $limit)
    {
        return $this->getTopLinkDao()->searchTopLinks($conditions, $orderBy, $start, $limit);
    }

    public function searchTopLinkCount(array $conditions)
    {
        return $this->getTopLinkDao()->searchTopLinkCount($conditions);
    }

    public function createTopLink($topLink)
    {
        $topLink['createdTime'] = time();
        return $this->getTopLinkDao()->addTopLink($topLink);
    }

    public function editTopLink($id,$fields)
    {
        $topLink = $this->getTopLink($id);

        if (empty($topLink)) {
            throw $this->createServiceException('顶部链接不存在，更新失败。');
        }

        $fields = ArrayToolkit::filter($fields, array(
            'name' => '',
            'url' => '',
        ));
        return $this->getTopLinkDao()->updateTopLink($id,$fields);
    }

    public function removeTopLink($id)
    {
        $topLink = $this->getTopLink($id);

        if (empty($topLink)) {
            throw $this->createServiceException('顶部链接不存在，删除失败。');
        }
        $this->getTopLinkDao()->deleteTopLink($id);
    }

    private function getTopLinkDao()
    {
        return $this->createDao('Custom:TopLink.TopLinkDao');
    }
}