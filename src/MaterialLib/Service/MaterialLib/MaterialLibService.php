<?php

namespace MaterialLib\Service\MaterialLib;

interface MaterialLibService
{
    public function search($conditions, $start, $limit);

    public function searchCount($conditions);
}
