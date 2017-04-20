<?php

namespace Biz\Card\DetailProcessor;

interface DetailProcessor
{
    public function getDetailById($id);

    public function getCardDetailsByCardIds($ids);
}
