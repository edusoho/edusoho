<?php

namespace MaterialLib\Service\MaterialLib;

interface MaterialLibService
{
    public function search($conditions, $sort, $start, $limit);

    public function searchCount($conditions);
}
