<?php

namespace Biz\EduCloud\Service;

interface ConsultService
{
    public function getAccount();

    public function getJsResource();

    public function buildCloudConsult($account, $jsResource);
}
