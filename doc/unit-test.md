# 单元测试

## 安装PHPUnit

    wget https://phar.phpunit.de/phpunit.phar
    chmod +x phpunit.phar
    sudo mv phpunit.phar /usr/local/bin/phpunit
    phpunit --version

## 创建测试数据库

进入mysql命令行，或者打开phpMyadmin，执行以下语句：

    # 创建edusoho-test数据库
    CREATE DATABASE `edusoho-test` DEFAULT CHARACTER SET utf8 ; 
    # 创建tester数据库用户，并授权访问edusoho-test数据库
    GRANT ALL PRIVILEGES ON `edusoho-test`.* TO 'tester'@'localhost' IDENTIFIED BY 'tester';
    # 刷新权限表
    FLUSH PRIVILEGES;

## 运行单元测试

进入到程序目录，比如/var/www/edusoho，执行：

    phpunit -c app/

-c 参数，指定PHPUnit配置文件所在目录，PHPUnit的默认配置文件是app/phpunit.xml.dist。
第一次运行PHPUnit的时候会有点慢，因为再创建数据库，请耐心等待。

**指定执行某个TestCase：**

    phpunit -c app/ FILEPATH


## 注意事项

请不要修改app/config/config_test.yml，app/phpunit.xml.dist配置，大家统一测试配置信息。