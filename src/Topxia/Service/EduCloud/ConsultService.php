<?php
namespace Topxia\Service\EduCloud;

interface ConsultService
{
    public function getAccount();

    public function getJsResource();

    public function buildCloudConsult($account, $jsResource);
}