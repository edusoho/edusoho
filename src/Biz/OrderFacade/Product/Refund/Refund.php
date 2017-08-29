<?php

namespace Biz\OrderFacade\Product\Refund;

interface Refund
{
    public function lockMember();

    public function removeMember();
}
