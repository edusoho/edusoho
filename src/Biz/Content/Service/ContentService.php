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
     * @Log(module="content",action="create")
     */
    public function createContent($content);

    public function updateContent($id, $content);

    public function trashContent($id);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="content",action="delete")
     */
    public function deleteContent($id);

    public function isAliasAvaliable($alias);
}
