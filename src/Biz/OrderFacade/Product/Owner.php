<?php

namespace Biz\OrderFacade\Product;

interface Owner
{
    public function exitOwner($userId);

    public function getOwner($userId);
}
