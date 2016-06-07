<?php

namespace Topxia\Service\Content\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Content\BlockTemplateService;
use Topxia\Common\ArrayToolkit;

class BlockTemplateServiceImpl extends BaseService implements BlockTemplateService
{
    public function getBlockTemplate($id)
    {
        $result = $this->getBlockTemplateDao()->getBlockTemplate($id);
        if (empty($result)) {
            return;
        }
         return $result;
    }

    public function searchBlockTemplateCount($condition)
    {
        return $this->getBlockTemplateDao()->searchBlockTemplateCount($condition);
    }

    public function searchBlockTemplates($conditions, $orderBy, $start, $limit)
    {
        return $this->getBlockTemplateDao()->searchBlockTemplates($conditions, $orderBy, $start, $limit);
    }

    protected function getBlockTemplateDao()
    {
        return $this->createDao('Content.BlockTemplateDao');
    }
}
