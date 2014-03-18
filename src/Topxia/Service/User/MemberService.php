<?php
namespace Topxia\Service\User;

interface MemberService
{
    public function checkMemberName($MemberName);
    public function isMemberNameAvaliable($MemberName);
    public function updateMemberLevel($userData);
}