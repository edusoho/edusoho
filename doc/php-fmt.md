### 1. 安装 php7.0 

#### 1.1  install on mac 
brew update
brew unlink php56
brew install php70 --with-fpm --with-gmp --with-homebrew-openssl --with-imap --with-intl --with-libmysql --without-bz2 --without-mysql --without-pcntl --without-pear --with-cli --with-curl --with-mcrypt --with-mysqlnd_ms --with-gd --without-apache
php -v
/usr/local/bin/php -v
brew unlink php70
brew link php56

#### 1.2 install on linux

### 2 配置文件
{
	"autocomplete": true,
	"enable_auto_align": true,
	"format_on_save": true,
	"indent_with_space": 4,
	"passes":
	[
		"SpaceBetweenMethods",              //方法之间添加空格
		"TightConcat",                      //连接符去掉空格
		"StripSpaceWithinControlStructures", //控制结构中去除空行
		"StripNewlineAfterCurlyOpen",    //移除方法括号空格
		"StripNewlineAfterClassOpen",   //移除类括号空格
		"StripExtraCommaInArray",       //移除数组内的最后一个逗号
		"SortUseNameSpace",             //对引用排序 
		"ReplaceBooleanAndOr",          //将单词替换为操作符
		"OrderAndRemoveUseClauses",  //移除没有使用的引用
		"PrettyPrintDocBlocks",
		"ClassToSelf",          //优先使用self
		"AlignGroupDoubleArrow", //对齐=>
		"AlignEquals", //对齐等号
		"MergeElseIf",
	],
	"php_bin": "/usr/local/opt/php70/bin/php", 
	"psr2": true,
	"version": 1
}
