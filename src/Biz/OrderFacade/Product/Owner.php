<?php

namespace Biz\OrderFacade\Product;

interface Owner
{
    public function removeOwner($userId);

    public function getOwner($userId);
}
