<?php

namespace Biz\InformationCollect\Service;

interface EventService
{
    public function count($conditions);

    public function search($conditions, $orderBy, $start, $limit);
}
