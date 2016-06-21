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

    public function deleteBlockTemplate($id)
    {
        return $this->getBlockTemplateDao()->deleteBlockTemplate($id);
    }

    public function getBlockTemplateByCode($code)
    {
        return $this->getBlockTemplateDao()->getBlockTemplateByCode($code);
    }

    public function updateBlockTemplate($id, $fields)
    {
        $block = $this->getBlockTemplateDao()->getBlockTemplate($id);

        if (!$block) {
            throw $this->createServiceException('此编辑区模板不存在，更新失败!');
        }
        $fields['updateTime'] = time();
        $updatedBlock = $this->getBlockTemplateDao()->updateBlockTemplate($id, $fields);

        $this->getLogService()->info('blockTemplate', 'update_block_template', "更新编辑区模板#{$id}");

        return $updatedBlock;
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

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
