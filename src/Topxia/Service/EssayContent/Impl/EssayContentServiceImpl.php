<?php
namespace Topxia\Service\EssayContent\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\EssayContent\EssayContentService;
use Topxia\Common\ArrayToolkit;

class EssayContentServiceImpl extends BaseService implements EssayContentService
{
    public function getEssayItems($articleId)
    {
        $contents = $this->getEssayContentDao()->findContentsByArticleId($articleId);
        $chapters = $this->getEssayChapterDao()->findChaptersByArticleId($articleId);

        $items = array();
        foreach ($contents as $content) {
            $content['itemType'] = 'content';
            $items["content-{$content['id']}"] = $content;
        }

        foreach ($chapters as $chapter) {
            $chapter['itemType'] = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort($items, function($item1, $item2){
            return $item1['seq'] > $item2['seq'];
        });
        return $items;
    }

    public function getChapter($articleId, $chapterId)
    {
        $chapter = $this->getEssayChapterDao()->getChapter($chapterId);
        if (empty($chapter) or $chapter['articleId'] != $articleId) {
            return null;
        }
        return $chapter;
    }

    public function getArticleChapters($articleId)
    {
        return $this->getEssayChapterDao()->findChaptersByArticleId($articleId);
    }


    public function createChapter($chapter)
    {
        if (!in_array($chapter['type'], array('chapter', 'unit'))) {
            throw $this->createServiceException("章节类型不正确，添加失败！");
        }

        if ($chapter['type'] == 'unit') {
            list($chapter['number'], $chapter['parentId']) = $this->getNextUnitNumberAndParentId($chapter['articleId']);
        } else {
            $chapter['number'] = $this->getNextChapterNumber($chapter['articleId']);
            $chapter['parentId'] = 0;
        }

        $chapter['seq'] = $this->getNextArticleItemSeq($chapter['articleId']);
        $chapter['createdTime'] = time();
        return $this->getEssayChapterDao()->addChapter($chapter);
    }

    public function sortEssayItems($articleId, array $itemIds)
    {
        $items = $this->getEssayItems($articleId);
        $existedItemIds = array_keys($items);

        if (count($itemIds) != count($existedItemIds)) {
            throw $this->createServiceException('itemdIds参数不正确');
        }

        $diffItemIds = array_diff($itemIds, array_keys($items));
        if (!empty($diffItemIds)) {
            throw $this->createServiceException('itemdIds参数不正确');
        }

        $contentNum = $chapterNum = $unitNum = $seq = 0;
        $currentChapter = $rootChapter = array('id' => 0);

        foreach ($itemIds as $itemId) {
            $seq ++;
            list($type, ) = explode('-', $itemId);
            switch ($type) {
                case 'content':
                    $contentNum ++;
                    $item = $items[$itemId];
                    $fields = array('number' => $contentNum, 'seq' => $seq, 'chapterId' => $currentChapter['id']);
                    if ($fields['number'] != $item['number'] or $fields['seq'] != $item['seq'] or $fields['chapterId'] != $item['chapterId']) {
                        $this->getEssayContentDao()->updateContent($item['id'], $fields);
                    }
                    break;
                case 'chapter':
                    $item = $currentChapter = $items[$itemId];
                    if ($item['type'] == 'unit') {
                        $unitNum ++;
                        $fields = array('number' => $unitNum, 'seq' => $seq, 'parentId' => $rootChapter['id']);
                    } else {
                        $chapterNum ++;
                        $unitNum = 0;
                        $rootChapter = $item;
                        $fields = array('number' => $chapterNum, 'seq' => $seq, 'parentId' => 0);
                    }
                    if ($fields['parentId'] != $item['parentId'] or $fields['number'] != $item['number'] or $fields['seq'] != $item['seq']) {
                        $this->getEssayChapterDao()->updateChapter($item['id'], $fields);
                    }
                    break;
            }
        }
    }

    public function updateChapter($articleId, $chapterId, $fields)
    {
        $chapter = $this->getChapter($articleId, $chapterId);
        if (empty($chapter)) {
            throw $this->createServiceException("章节#{$chapterId}不存在！");
        }
        $fields = ArrayToolkit::parts($fields, array('title'));
        return $this->getEssayChapterDao()->updateChapter($chapterId, $fields);
    }

    public function deleteChapter($articleId, $chapterId)
    {
        $deletedChapter = $this->getChapter($articleId, $chapterId);
        if (empty($deletedChapter)) {
            throw $this->createServiceException(sprintf('章节(ID:%s)不存在，删除失败！', $chapterId));
        }

        $this->getEssayChapterDao()->deleteChapter($deletedChapter['id']);

        $prevChapter = array('id' => 0);
        foreach ($this->getArticleChapters($articleId) as $chapter) {
            if ($chapter['number'] < $deletedChapter['number']) {
                $prevChapter = $chapter;
            }
        }

        $contents = $this->getEssayContentDao()->findContentsByChapterId($deletedChapter['id']);
        foreach ($contents as $content) {
            $this->getEssayContentDao()->updateContent($content['id'], array('chapterId' => $prevChapter['id']));
        }
    }

    public function getNextChapterNumber($articleId)
    {
        $counter = $this->getEssayChapterDao()->getChapterCountByArticleIdAndType($articleId, 'chapter');
        return $counter + 1;
    }

    public function getNextUnitNumberAndParentId($articleId)
    {
        $lastChapter = $this->getEssayChapterDao()->getLastChapterByArticleIdAndType($articleId, 'chapter');

        $parentId = empty($lastChapter) ? 0 : $lastChapter['id'];

        $unitNum = 1 + $this->getEssayChapterDao()->getChapterCountByArticleIdAndTypeAndParentId($articleId, 'unit', $parentId);

        return array($unitNum, $parentId);
    }

    private function getNextArticleItemSeq($articleId)
    {
        $chapterMaxSeq = $this->getEssayChapterDao()->getChapterMaxSeqByArticleId($articleId);
        $contentMaxSeq = $this->getEssayContentDao()->getContentMaxSeqByArticleId($articleId);
        return ($chapterMaxSeq > $contentMaxSeq ? $chapterMaxSeq : $contentMaxSeq) + 1;
    }

    private function getEssayContentDao() 
    {
        return $this->createDao('EssayContent.EssayContentDao');
    }

    private function getEssayChapterDao() 
    {
        return $this->createDao('EssayContent.EssayChapterDao');
    }
}
