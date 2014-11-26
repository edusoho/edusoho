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

    private function getEssayContentDao() 
    {
        return $this->createDao('EssayContent.EssayContentDao');
    }

    private function getEssayChapterDao() 
    {
        return $this->createDao('EssayContent.EssayChapterDao');
    }
}
