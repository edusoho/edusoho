<?php
namespace Topxia\Service\EssayContent\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\EssayContent\EssayContentService;
use Topxia\Common\ArrayToolkit;

class EssayContentServiceImpl extends BaseService implements EssayContentService
{
    public function getEssayItems($essayId)
    {
        $contents = $this->getEssayContentDao()->findContentsByArticleId($essayId);
        $chapters = $this->getEssayChapterDao()->findChaptersByArticleId($essayId);

        $items = array();
        foreach ($contents as $content) {
            $content['itemType'] = 'content';
            $articleMaterial = $this->getArticleMaterialDao()->getArticleMaterial($content['materialId']);
            $content['title'] = $articleMaterial['title'];
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

    public function getChapter($essayId, $chapterId)
    {
        $chapter = $this->getEssayChapterDao()->getChapter($chapterId);
        if (empty($chapter) or $chapter['articleId'] != $essayId) {
            return null;
        }
        return $chapter;
    }

    public function getContent($essayId, $contentId)
    {
        $content = $this->getEssayContentDao()->getContent($contentId);
        if (empty($content) or $content['articleId'] != $essayId) {
            return null;
        }
        return $content;
    }

    public function getArticleChapters($essayId)
    {
        return $this->getEssayChapterDao()->findChaptersByArticleId($essayId);
    }

    public function getArticleContents($essayId)
    {
        return $this->getEssayContentDao()->findContentsByArticleId($essayId);
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

    public function createContent($fields)
    {
        $content = ArrayToolkit::filter($fields, array(
            'articleId' => 0,
            'chapterId' => 0,
            'materialId' => '',
        ));

        $content['number'] = $this->getNextContentNumber($content['articleId']);
        $content['seq'] = $this->getNextArticleItemSeq($content['articleId']);
        $content['userId'] = $this->getCurrentUser()->id;
        $content['createdTime'] = time();

        $lastChapter = $this->getEssayChapterDao()->getLastChapterByArticleId($content['articleId']);
        $content['chapterId'] = empty($lastChapter) ? 0 : $lastChapter['id'];

        $content = $this->getEssayContentDao()->addContent($content);
    }

    public function sortEssayItems($essayId, array $itemIds)
    {
        $items = $this->getEssayItems($essayId);
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

    public function updateChapter($essayId, $chapterId, $fields)
    {
        $chapter = $this->getChapter($essayId, $chapterId);
        if (empty($chapter)) {
            throw $this->createServiceException("章节#{$chapterId}不存在！");
        }
        $fields = ArrayToolkit::parts($fields, array('title'));
        return $this->getEssayChapterDao()->updateChapter($chapterId, $fields);
    }

    public function updateContent($contentId,$materialId)
    {       
        return $this->getEssayContentDao()->updateContent($contentId, $materialId);
    }

    public function deleteChapter($essayId, $chapterId)
    {
        $deletedChapter = $this->getChapter($essayId, $chapterId);
        if (empty($deletedChapter)) {
            throw $this->createServiceException(sprintf('章节(ID:%s)不存在，删除失败！', $chapterId));
        }

        $this->getEssayChapterDao()->deleteChapter($deletedChapter['id']);

        $prevChapter = array('id' => 0);
        foreach ($this->getArticleChapters($essayId) as $chapter) {
            if ($chapter['number'] < $deletedChapter['number']) {
                $prevChapter = $chapter;
            }
        }

        $contents = $this->getEssayContentDao()->findContentsByChapterId($deletedChapter['id']);
        foreach ($contents as $content) {
            $this->getEssayContentDao()->updateContent($content['id'], array('chapterId' => $prevChapter['id']));
        }
    }

    public function deleteContent($essayId, $contentId)
    {
        $deletedContent = $this->getContent($essayId, $contentId);
        if (empty($deletedContent)) {
            throw $this->createServiceException(sprintf('素材(ID:%s)不存在，删除失败！', $contentId));
        }

        $this->getEssayContentDao()->deleteContent($deletedContent['id']);
        $prevContent = array('id' => 0);
        foreach ($this->getArticleContents($essayId) as $content) {
            if ($content['number'] > $deletedContent['number']) {
                $prevContent = $content;
                $this->getEssayContentDao()->updateContent($content['id'], array('number' => $prevContent['number']-1));
            }
        }
    }

    public function getNextChapterNumber($essayId)
    {
        $counter = $this->getEssayChapterDao()->getChapterCountByArticleIdAndType($essayId, 'chapter');
        return $counter + 1;
    }

    public function getNextContentNumber($essayId)
    {
        return $this->getEssayContentDao()->getContentCountByArticleId($essayId) + 1;
    }

    public function getNextUnitNumberAndParentId($essayId)
    {
        $lastChapter = $this->getEssayChapterDao()->getLastChapterByArticleIdAndType($essayId, 'chapter');

        $parentId = empty($lastChapter) ? 0 : $lastChapter['id'];

        $unitNum = 1 + $this->getEssayChapterDao()->getChapterCountByArticleIdAndTypeAndParentId($essayId, 'unit', $parentId);

        return array($unitNum, $parentId);
    }

    private function getNextArticleItemSeq($essayId)
    {
        $chapterMaxSeq = $this->getEssayChapterDao()->getChapterMaxSeqByArticleId($essayId);
        $contentMaxSeq = $this->getEssayContentDao()->getContentMaxSeqByArticleId($essayId);
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

    private function getArticleMaterialDao()
    {
        return $this->createDao('ArticleMaterial.ArticleMaterialDao');
    }
}
