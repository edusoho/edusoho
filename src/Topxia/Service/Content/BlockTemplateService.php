<?php

namespace Topxia\Service\Content;

interface BlockTemplateService
{
    public function getBlockTemplate($id);

    public function searchBlockTemplates($conditions, $orderBy, $start, $limit);

    public function searchBlockTemplateCount($condition);

    public function getBlockTemplateByCode($code);
}