<?php
/**
 * Created by PhpStorm.
 * User: ilham
 * Date: 5/5/18
 * Time: 8:35 PM
 */

namespace Biz\Mp\Service;

interface MpService
{
    public function getMpSdk();

    public function getAuthorization();

    public function generateInitUrl($params, $schema);

    public function generateVersionManagementUrl($params, $schema);
}
