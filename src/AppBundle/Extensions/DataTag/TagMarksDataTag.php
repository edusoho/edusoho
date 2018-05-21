<?php

namespace AppBundle\Extensions\DataTag;

class TagMarksDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $tagMarks = array();

        krsort($arguments['tags']);

        foreach ($arguments['tags'] as $groupId => $tagId) {
            $tag = $this->getTagService()->getTag($tagId);
            $tagName = $tag['name'];

            $tagMarks[] = array(
                'tagName' => $tagName,
                'tagId' => $tagId,
                'groupId' => $groupId,
            );
        }

        return $tagMarks;
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }
}
