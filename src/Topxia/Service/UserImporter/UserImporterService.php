<?php
namespace Topxia\Service\UserImporter;


interface UserImporterService
{
    public function importUsers(array $users);

    public function importUpdateNickname(array $users);

    public function importUpdateEmail(array $users);
}
