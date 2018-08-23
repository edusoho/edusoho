<?php

namespace Biz\System\Service;

interface SettingService
{
    public function set($name, $value);

    public function setByNamespace($namespace, $name, $value);

    public function get($name, $default = null);

    public function node($name, $default = null);

    public function delete($name);

    public function deleteByNamespaceAndName($namespace, $name);

    public function isReservationOpen();
}
