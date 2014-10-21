<?php
namespace Topxia\Service\Content\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Content\BlockService;
use Topxia\Common\ArrayToolkit;

class BlockServiceImpl extends BaseService implements BlockService
{

    public function searchBlockCount()
    {
        return $this->getBlockDao()->searchBlockCount();
    }

    public function findBlockHistoryCountByBlockId($blockId)
    {
        return $this->getBlockHistoryDao()->findBlockHistoryCountByBlockId($blockId);
    }

    public function getLatestBlockHistory()
    {
        return  $this->getBlockHistoryDao()->getLatestBlockHistory();
    }

    public function getBlock($id)
    {
        $result = $this->getBlockDao()->getBlock($id);
        if(!$result){
            return null;
        } else{
            return $result;
        }
    }

    public function getBlockHistory($id)
    {
        return $this->getBlockHistoryDao()->getBlockHistory($id);
    }

    public function generateBlockTemplateItems($block)
    {  

        preg_match_all("/\(\((.+?)\)\)/", $block['template'], $matches);
        while (list($key, $value) = each($matches[1])){
            $matches[1][$key] = trim($value);
        };

        $templateDatas = ($matches[1]) ? ($matches[1]) : '';
        $templateItems = array();

        if (empty($templateDatas)) {
            return $templateItems;
        } else {
            foreach ($templateDatas as &$item) {
                $item = explode(":", $item);
                $arr[] = array( 'title' => $item[0],'type' => $item[1] );
            }

            $templateItems = ArrayToolkit::index($arr, 'title');
            $templateItems = array_values($templateItems);
            return $templateItems;
        }
    }

    public function getBlockByCode($code)
    {

        $result = $this->getBlockDao()->getBlockByCode($code);
        if(!$result){
            return null;
        } else{
            return $result;
        }
    }

    public function searchBlocks($start, $limit)
    {
        return $this->getBlockDao()->findBlocks($start, $limit);
    }

    public function findBlockHistorysByBlockId($blockId, $start, $limit)
    {
        return $this->getBlockHistoryDao()->findBlockHistorysByBlockId($blockId, $start, $limit);
    }

    public function createBlock($block)
    {   
        if (!ArrayToolkit::requireds($block, array('code', 'title'))) {
            throw $this->createServiceException("创建编辑区失败，缺少必要的字段");
        }

        $user = $this->getCurrentUser();
        $block['userId'] = $user['id'];
        $block['tips'] = empty($block['tips']) ? '' : $block['tips'];
        $block['createdTime'] = time();
        $block['updateTime'] = time();
        $createdBlock = $this->getBlockDao()->addBlock($block);

        $blockHistoryInfo = array(
            'blockId'=>$createdBlock['id'],
            'content'=>$createdBlock['content'],
            'userId'=>$createdBlock['userId'],
            'createdTime'=>time()
            );
        $this->getBlockHistoryDao()->addBlockHistory($blockHistoryInfo);
        return $createdBlock;
    }

    public function updateBlock($id, $fields)
    {   
        $block = $this->getBlockDao()->getBlock($id);
        $user = $this->getCurrentUser();

        if (!$block) {
            throw $this->createServiceException("此编辑区不存在，更新失败!");
        }
        $fields['updateTime'] = time();
        $updatedBlock = $this->getBlockDao()->updateBlock($id, $fields);

        $blockHistoryInfo = array(
            'blockId'=>$updatedBlock['id'],
            'content'=>$updatedBlock['content'],
            'templateData'=>$updatedBlock['templateData'],
            'userId'=>$user['id'],
            'createdTime'=>time()
        );
        $this->getBlockHistoryDao()->addBlockHistory($blockHistoryInfo);

        $this->getLogService()->info('block', 'update', "更新编辑区#{$id}", array('content' => $updatedBlock['content']));
        return $updatedBlock;
    }

    public function deleteBlock($id)
    {
        $block = $this->getBlockDao()->getBlock($id);
        $this->getBlockHistoryDao()->deleteBlockHistoryByBlockId($block['id']);
        return $this->getBlockDao()->deleteBlock($id);
    }

    public function getContentsByCodes(array $codes)
    {
        if(empty($codes)){
            throw $this->createServiceException("获取内容失败，不允许查询空编号所对应的内容!");
        }
        $contents = array();
        foreach ($codes as $key => $value) {
            $block = $this->getBlockDao()->getBlockByCode($value);
            if($block){
                $contents[$value] = $block['content'];
            } else {
                $contents[$value] = '';
            }
        }
        return $contents;
    }

    public function updateContent($id, $content)
    {
        $block = $this->getBlockDao()->getBlock($id);
        if (!$block) {
            throw $this->createServiceException("此编辑区不存在，更新失败!");
        }

        // $content = $this->purifyHtml($content);
        return $this->getBlockDao()->updateBlock($id, array('content'=>$content));
    }

    private function getBlockDao()
    {
        return $this->createDao('Content.BlockDao');
    }

    private function getBlockHistoryDao()
    {
        return $this->createDao('Content.BlockHistoryDao');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
