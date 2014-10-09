<?php
namespace Topxia\MobileBundleV2\Service;

interface UserService
{
	public function getVersion();
	public function login();
	public function regist();
	public function loginWithToken();
	public function getUserInfo();
	public function logout();

	public function getUserNotification();
}