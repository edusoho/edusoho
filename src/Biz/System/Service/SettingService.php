<?php

namespace Biz\System\Service;

use Biz\System\Annotation\Log;

interface SettingService
{
    /**
     * @param $name
     * @param $value
     *
     * @return mixed
     * @Log(module="system",action="update_settings",funcName="get",param="name",postfix="name")
     */
    public function set($name, $value);

    public function setByNamespace($namespace, $name, $value);

    public function get($name, $default = array());

    public function node($name, $default = null);

    public function delete($name);

    public function deleteByNamespaceAndName($namespace, $name);

    public function isReservationOpen();
}
