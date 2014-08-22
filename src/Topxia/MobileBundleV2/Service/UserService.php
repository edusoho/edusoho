<?php
namespace Topxia\MobileBundleV2\Service;

interface UserService
{
	public function getVersion();
	public function login();
	public function regist();
	public function loginWithToken();
}