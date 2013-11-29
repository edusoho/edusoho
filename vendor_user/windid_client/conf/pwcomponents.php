<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */

return array(
	'link' => array(
		'class' => 'SRV:link.srv.PwLinkService',
		'method' => 'getLinksByType',
	),
	'announce' => array(
		'class' => 'SRV:announce.srv.PwAnnounceService',
		'method' => 'getAnnounceForBbsScroll',
	)
);