<?php

namespace Topxia\Service\Sale\Dao;

interface OffSaleDao
{

    public function getOffsale($id);

    public function findOffsalesByIds(array $ids);

    public function addOffsale($member);

    public function updateOffsale($id, $member);

    public function deleteOffsale($id);

   
}