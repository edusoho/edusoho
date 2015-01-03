<?php
namespace Topxia\MobileBundleV2\Processor;

interface UserProcessor
{
	public function getVersion();
	public function login();
	public function regist();
	public function loginWithToken();
	public function getUserInfo();
	public function logout();

	public function getUserNotification();

	public function getUserLastlearning();

	public function getUserMessages();

	public function getMessageList();

	public function sendMessage();
}