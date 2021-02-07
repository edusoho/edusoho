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

brew services restart nginx php@7.4

```


