<?php

namespace Biz\Content\Service\Impl;

use Biz\BaseService;
use Biz\Content\Dao\BlockDao;
use Biz\Content\Dao\BlockHistoryDao;
use Biz\Content\Dao\BlockTemplateDao;
use Biz\Content\Service\BlockService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use AppBundle\Common\ArrayToolkit;

class BlockServiceImpl extends BaseService implements BlockService
{
    public function createBlockTemplate($blockTemplate)
    {
        if (!ArrayToolkit::requireds($blockTemplate, array('code', 'mode', 'category', 'meta', 'data', 'templateName', 'title'))) {
            throw $this->createServiceException('创建编辑区失败，缺少必要的字段');
        }
        $createdBlockTemplate = $this->getBlockTemplateDao()->create($blockTemplate);

        return $createdBlockTemplate;
    }

    public function findBlockHistoryCountByBlockId($blockId)
    {
        return $this->getBlockHistoryDao()->countByBlockId($blockId);
    }

    public function getLatestBlockHistory()
    {
        return $this->getBlockHistoryDao()->getLatest();
    }

    public function getLatestBlockHistoriesByBlockIds($blockIds)
    {
        $blockHistories = array();
        foreach ($blockIds as $key => $blockId) {
            $block = $this->getBlockDao()->get($blockId);
            $blockHistories[$block['blockTemplateId']] = $this->getBlockHistoryDao()->getLatestByBlockId($blockId);
        }

        return $blockHistories;
    }

    public function getBlock($id)
    {
        $result = $this->getBlockDao()->get($id);
        if (empty($result)) {
            $blockTemplate = $this->getBlockTemplate($id);

            if (empty($blockTemplate)) {
                throw $this->createNotFoundException('block template not found');
            }

            $blockTemplate['blockTemplateId'] = $blockTemplate['id'];
            $blockTemplate['blockId'] = 0;

            return $blockTemplate;
        } else {
            $blockTemplate = $this->getBlockTemplate($result['blockTemplateId']);
            $result['meta'] = $blockTemplate['meta'];
            $result['blockId'] = $result['id'];
            $result['blockTemplateId'] = $blockTemplate['id'];

            return $result;
        }
    }

    public function getBlockHistory($id)
    {
        return $this->getBlockHistoryDao()->get($id);
    }

    public function generateBlockTemplateItems($block)
    {
        preg_match_all("/\(\((.+?)\)\)/", $block['template'], $matches);
        while (list($key, $value) = each($matches[1])) {
            $matches[1][$key] = trim($value);
        }

        $templateDatas = ($matches[1]) ? ($matches[1]) : '';
        $templateItems = array();

        if (empty($templateDatas)) {
            return $templateItems;
        } else {
            $arr = array();
            foreach ($templateDatas as &$item) {
                $item = explode(':', $item);
                $arr[] = array('title' => $item[0], 'type' => $item[1]);
            }

            $templateItems = ArrayToolkit::index($arr, 'title');
            $templateItems = array_values($templateItems);

            return $templateItems;
        }
    }

    public function getBlockByCode($code)
    {
        $user = $this->getCurrentUser();

        $result = $this->getBlockDao()->getByCodeAndOrgId($code, $user->getSelectOrgId());
        if (empty($result)) {
            $blockTemplate = $this->getBlockTemplateByCode($code);
            $blockTemplate['blockTemplateId'] = !empty($blockTemplate) ? $blockTemplate['id'] : 0;
            $blockTemplate['blockId'] = 0;

            return $blockTemplate;
        } else {
            $blockTemplate = $this->getBlockTemplate($result['blockTemplateId']);
            $result['meta'] = $blockTemplate['meta'];
            $result['mode'] = $blockTemplate['mode'];
            $result['templateName'] = $blockTemplate['templateName'];
            $result['blockId'] = $result['id'];
            $result['blockTemplateId'] = $blockTemplate['id'];

            return $result;
        }
    }

    public function findBlockHistorysByBlockId($blockId, $start, $limit)
    {
        return $this->getBlockHistoryDao()->findByBlockId($blockId, $start, $limit);
    }

    public function getBlocksByBlockTemplateIdsAndOrgId($blockTemplateIds, $orgId)
    {
        $blocks = array();
        foreach ($blockTemplateIds as $key => $blockTemplateId) {
            $blocks[] = $this->getBlockDao()->getByTemplateIdAndOrgId($blockTemplateId, $orgId);
        }

        return $blocks;
    }

    public function createBlock($block)
    {
        if (!ArrayToolkit::requireds($block, array('code', 'data', 'content', 'blockTemplateId'))) {
            throw $this->createInvalidArgumentException('创建编辑区失败，缺少必要的字段');
        }
        $user = $this->getCurrentUser();
        $block['createdTime'] = time();
        $block['updateTime'] = time();
        $block['userId'] = $user['id'];
        $block['orgId'] = $user['orgId'];
        unset($block['mode']);
        $createdBlock = $this->getBlockDao()->create($block);

        $blockHistoryInfo = array(
            'blockId' => $createdBlock['id'],
            'content' => $createdBlock['content'],
            'userId' => $createdBlock['userId'],
            'createdTime' => time(),
        );

        $this->getBlockHistoryDao()->create($blockHistoryInfo);

        $blockTemplate = $this->getBlockTemplateDao()->get($createdBlock['blockTemplateId']);
        $createdBlock['id'] = $blockTemplate['id'];
        $createdBlock['title'] = $blockTemplate['title'];
        $createdBlock['mode'] = $blockTemplate['mode'];

        return $createdBlock;
    }

    public function updateBlock($id, $fields)
    {
        $block = $this->getBlockDao()->get($id);
        $user = $this->getCurrentUser();

        if (!$block) {
            throw $this->createNotFoundException('此编辑区不存在，更新失败!');
        }
        $fields['updateTime'] = time();
        unset($fields['mode']);
        $updatedBlock = $this->getBlockDao()->update($id, $fields);

        $blockHistoryInfo = array(
            'blockId' => $updatedBlock['id'],
            'content' => $updatedBlock['content'],
            'data' => $updatedBlock['data'],
            'userId' => $user['id'],
            'createdTime' => time(),
        );

        $this->getBlockHistoryDao()->create($blockHistoryInfo);

        $this->getLogService()->info('system', 'update_block', "更新编辑区#{$id}", array('content' => $updatedBlock['content']));

        $blockTemplate = $this->getBlockTemplateDao()->get($updatedBlock['blockTemplateId']);
        $updatedBlock['id'] = $blockTemplate['id'];
        $updatedBlock['title'] = $blockTemplate['title'];
        $updatedBlock['mode'] = $blockTemplate['mode'];

        return $updatedBlock;
    }

    public function deleteBlock($id)
    {
        $block = $this->getBlockDao()->get($id);
        $this->getBlockHistoryDao()->deleteByBlockId($block['id']);

        return $this->getBlockDao()->delete($id);
    }

    public function getContentsByCodes(array $codes)
    {
        if (empty($codes)) {
            throw $this->createInvalidArgumentException('获取内容失败，不允许查询空编号所对应的内容!');
        }

        $cdn = $this->getSettingService()->get('cdn');
        $cdnUrl = empty($cdn['enabled']) ? '' : $cdn['url'];

        $contents = array();
        foreach ($codes as $key => $value) {
            $block = $this->getBlockDao()->getByCode($value);
            if ($block) {
                if ($cdnUrl) {
                    $contents[$value] = preg_replace('/\<img(\s+)src=\"\/files\//', "<img src=\"{$cdnUrl}/files/", $block['content']);
                } else {
                    $contents[$value] = $block['content'];
                }
            } else {
                $contents[$value] = '';
            }
        }

        return $contents;
    }

    public function updateTemplateContent($id, $content)
    {
        $blockTemplate = $this->getBlockTemplateDao()->get($id);
        if (!$blockTemplate) {
            throw $this->createServiceException('此编辑区不存在，更新失败!');
        }

        return $this->getBlockTemplateDao()->update($id, array(
            'content' => $content,
        ));
    }

    public function recovery($blockId, $history)
    {
        $block = $this->getBlockDao()->get($blockId);
        $blockTemplate = $this->getBlockTemplateDao()->get($block['blockTemplateId']);
        if (empty($block)) {
            throw $this->createNotFoundException('此编辑区不存在，更新失败!');
        }

        if ('template' == $blockTemplate['mode'] && empty($history['data'])) {
            throw $this->createNotFoundException('此编辑区数据不存在，更新失败!');
        }

        return $this->getBlockDao()->update($blockId, array(
            'content' => $history['content'],
            'data' => $history['data'],
        ));
    }

    public function getBlockByTemplateIdAndOrgId($blockTemplateId, $orgId = 0)
    {
        $block = $this->getBlockDao()->getByTemplateIdAndOrgId($blockTemplateId, $orgId);
        if (empty($block)) {
            $blockTemplate = $this->getBlockTemplate($blockTemplateId);
            $blockTemplate['blockTemplateId'] = $blockTemplate['id'];
            $blockTemplate['blockId'] = 0;

            return $blockTemplate;
        }
        $blockTemplate = $this->getBlockTemplate($blockTemplateId);
        $block['blockId'] = $block['id'];
        $block['blockTemplateId'] = $blockTemplate['id'];
        $block['code'] = $blockTemplate['code'];
        $block['template'] = $blockTemplate['template'];
        $block['tips'] = $blockTemplate['tips'];
        $block['mode'] = $blockTemplate['mode'];
        $block['meta'] = $blockTemplate['meta'];
        $block['title'] = $blockTemplate['title'];
        $block['templateName'] = $blockTemplate['templateName'];

        return $block;
    }

    public function getBlockTemplate($id)
    {
        $result = $this->getBlockTemplateDao()->get($id);
        if (empty($result)) {
            return array();
        }

        return $result;
    }

    public function deleteBlockTemplate($id)
    {
        $block = $this->getBlockDao()->getByTemplateId($id);
        $this->getBlockHistoryDao()->deleteByBlockId($block['id']);
        $this->getBlockDao()->delete($block['id']);

        return $this->getBlockTemplateDao()->delete($id);
    }

    public function getBlockTemplateByCode($code)
    {
        return $this->getBlockTemplateDao()->getByCode($code);
    }

    public function updateBlockTemplate($id, $fields)
    {
        $blockTemplate = $this->getBlockTemplateDao()->get($id);

        if (empty($blockTemplate)) {
            throw $this->createNotFoundException('此编辑区模板不存在，更新失败!');
        }
        $updatedBlockTemplate = $this->getBlockTemplateDao()->update($id, $fields);

        $this->getLogService()->info('blockTemplate', 'update_block_template', "更新编辑区模板#{$id}");

        return $updatedBlockTemplate;
    }

    public function searchBlockTemplateCount($condition)
    {
        return $this->getBlockTemplateDao()->count($condition);
    }

    public function searchBlockTemplates($conditions, $orderBy, $start, $limit)
    {
        return $this->getBlockTemplateDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function getPosters()
    {
        $posters = array();
        $theme = $this->getSettingService()->get('theme', array());
        $topBanner = $this->getBlockDao()->getByCode($theme['uri'].':home_top_banner');
        if (empty($topBanner)) {
            $topBanner = $this->getBlockTemplateDao()->getByCode($theme['uri'].':home_top_banner');
        }
        if (!empty($topBanner)) {
            $data = $topBanner['data'];
        }
        if (empty($data['posters'])) {
            return $posters;
        }
        foreach ($data['posters'] as $poster) {
            if (1 == $poster['status']) {
                $item = array(
                    'image' => $poster['src'],
                    'link' => array('type' => 'url', 'url' => $poster['href']),
                );
                array_push($posters, $item);
            }
        }

        return $posters;
    }

    /**
     * @return BlockTemplateDao
     */
    protected function getBlockTemplateDao()
    {
        return $this->createDao('Content:BlockTemplateDao');
    }

    /**
     * @return BlockDao
     */
    protected function getBlockDao()
    {
        return $this->createDao('Content:BlockDao');
    }

    /**
     * @return BlockHistoryDao
     */
    protected function getBlockHistoryDao()
    {
        return $this->createDao('Content:BlockHistoryDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
