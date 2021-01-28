<?php

namespace Biz\Content\Service;

interface BlockService
{
    public function getBlock($id);

    public function createBlockTemplate($blockTemplate);

    public function getBlockByTemplateIdAndOrgId($blockTemplateId, $orgId = 0);

    public function getBlockByCode($code);

    public function findBlockHistorysByBlockId($blockId, $start, $limit);

    public function getBlocksByBlockTemplateIdsAndOrgId($blockTemplateIds, $orgId);

    public function findBlockHistoryCountByBlockId($blockId);

    public function getBlockHistory($id);

    public function getLatestBlockHistory();

    public function getLatestBlockHistoriesByBlockIds($blockIds);

    public function createBlock($block);

    public function updateBlock($id, $fields);

    public function deleteBlock($id);

    public function updateTemplateContent($id, $content);

    public function recovery($blockId, $history);

    public function getBlockTemplate($id);

    public function searchBlockTemplates($conditions, $orderBy, $start, $limit);

    public function searchBlockTemplateCount($condition);

    public function getBlockTemplateByCode($code);

    public function deleteBlockTemplate($id);

    public function updateBlockTemplate($id, $fields);

    /**
     * 批量获取指定code的，编辑区块内容。
     *
     * @param array $codes 编号列表
     *
     * @return array 以code为key，编辑区内容为value的内容。
     *               $codes = array('homepage-top-bannner', 'site-bottom-banner');
     *               array(
     *               'homepage-top-bannner' => 'xxxxxxx',
     *               'site-bottom-banner' => 'xxxxxxx',
     *               'xxxxxqqqq' => '',
     *               );
     */
    public function getContentsByCodes(array $codes);

    public function getPosters();
}
