<?php

namespace Topxia\Service\Card\DetailProcessor;

interface DetailProcessor
{
    public function getDetailById($id);

    public function getCardDetailsByCardIds($ids);

}
