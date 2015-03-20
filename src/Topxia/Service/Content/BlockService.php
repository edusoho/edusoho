<?php
namespace Topxia\Service\Content;

interface BlockService
{

	public function getBlock($id);

	public function getBlockByCode($code);

	public function searchBlocks($start, $limit);

	public function searchBlockCount();

	public function findBlockHistorysByBlockId($blockId, $start, $limit);

	public function findBlockHistoryCountByBlockId($blockId);

	public function generateBlockTemplateItems($block);

	public function getBlockHistory($id);

	public function getLatestBlockHistory();
	
	public function createBlock($block);

	public function updateBlock($id, $fields);

	public function deleteBlock($id);

	public function updateContent($id, $content);

	/**
	 * 批量获取指定code的，编辑区块内容。
	 * @param  array  $codes 编号列表
	 * @return array 以code为key，编辑区内容为value的内容。
	 *         $codes = array('homepage-top-bannner', 'site-bottom-banner');
	 *         array(
	 *         		'homepage-top-bannner' => 'xxxxxxx',
	 *         		'site-bottom-banner' => 'xxxxxxx',
	 *         		'xxxxxqqqq' => '',
	 *         );
	 */
	public function getContentsByCodes(array $codes);
}