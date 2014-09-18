<?php
namespace Topxia\Service\UserImporter;


interface ParentImporterService
{
    public function importUsers(array $users);

    public function importUpdateNumber(array $users);

    public function importUpdateEmail(array $users);
}
