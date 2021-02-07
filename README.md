# MacOS开发环境配置
[edusoho开发文档](http://developer.edusoho.com/setup/setup-edusoho.html)
<br/>
[Installing Homebrew PHP extensions with PECL](https://grrr.tech/posts/installing-homebrew-php-extensions-with-pecl/)

## 补充一下edusoho的环境配置
```shell
brew install php@7.4 nginx

pecl config-get ext_dir | pbcopy
chmod 777 `clipboard data`
pecl install xdebug 

cp ./devenv/servers/edusoho.conf /usr/local/etc/nginx/servers/

mkdir -p /var/www

brew services restart php@7.4
brew services restart nginx 

```

## xdebug 504 error

### set /usr/local/etc/nginx/nginx.conf
```apacheconf
http {
    fastcgi_read_timeout 3600;
}
```


### set this line in php.ini:
```shell
php -i |grep php.ini
```

```ini
request_terminate_timeout = 3600s

[xdebug]
zend_extension="xdebug.so"
xdebug.idekey="PHPSTORM"    #会话需要的key
xdebug.mode=debug
xdebug.client_host=127.0.0.1
xdebug.file_link_format='phpstorm://open?file=%f&line=%l'
```

