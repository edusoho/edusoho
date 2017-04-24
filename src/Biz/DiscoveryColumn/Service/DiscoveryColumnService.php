<?php

namespace Biz\DiscoveryColumn\Service;

interface DiscoveryColumnService
{
    public function getDiscoveryColumn($id);

    public function updateDiscoveryColumn($id, $fields);

    public function addDiscoveryColumn($fields);

    public function deleteDiscoveryColumn($id);

    public function findDiscoveryColumnByTitle($title);

    public function getAllDiscoveryColumns();

    public function sortDiscoveryColumns(array $columnIds);

    public function getDisplayData();
}
