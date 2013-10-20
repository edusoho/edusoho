<?php 
defined('WEKIT_VERSION') or exit(403);
return array(
/**
 * 全局应用部署目录配置
 */


/**=====配置开始于此=====**/


/**
 * 源代码库目录,路径相对于wekit.php文件所在目录
 */
'ROOT'		 => '..',
'CONF'       => '../conf',
'DATA'       => '../data',
'SRC'        => '../src',

'APPS'       => '../src/applications',
'EXT'   	 => '../src/extensions',
'HOOK'		 => '../src/hooks',
'LIB'        => '../src/library',
'SRV'		 => '../src/service',
'REP'		 => '../src/repository',
'WINDID'	 => '../src/windid',
'ACLOUD'	 => '../src/aCloud',
'ADMIN'		 => '../src/applications/admin',
'APPCENTER'	 => '../src/applications/appcenter',
/**
 * 可访问目录
 */

'PUBLIC'     => '..',
'THEMES'     => '../themes',
'TPL'        => '../template',
'ATTACH'	 => '../attachment',
'HTML'       => '../html',



/**=====配置结束于此=====**/

);?>