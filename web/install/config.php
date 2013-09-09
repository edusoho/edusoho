<?php
function _getLowestEnvironment() {
	return array(
		'os' => '不限制',
		'version' => '5.1.2',
		'mysql' => '4.2',
		'pdo_mysql' => '必须',
		'upload' => '不限制',
		'space' => '50M');
}

function _getRecommendEnvironment() {
	return array(
		'os' => '类UNIX',
		'version' => '>5.3.x',
		'mysql' => '>5.x.x',
		'pdo_mysql' => '必须',
		'upload' => '>2M',
		'space' => '>50M');
}
