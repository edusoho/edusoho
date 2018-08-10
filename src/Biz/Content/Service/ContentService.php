<?php

namespace Biz\Content\Service;

use Biz\System\Annotation\Log;

interface ContentService
{
    public function getContent($id);

    public function getContentByAlias($alias);

    public function searchContents($conditions, $orderBy, $start, $limit);

    public function searchContentCount($conditions);

    /**
     * @param $fields
     *
     * @return mixed
     * @Log(level="info",module="content",action="create",message="创建内容",targetType="content",param="result")
     */
    public function createContent($content);

    public function updateContent($id, $content);

    public function trashContent($id);

    public function deleteContent($id);

    public function isAliasAvaliable($alias);
}
