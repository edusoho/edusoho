# XXTEA 加密算法的 PHP 实现

<a href="https://github.com/xxtea/">
    <img src="https://avatars1.githubusercontent.com/u/6683159?v=3&s=86" alt="XXTEA logo" title="XXTEA" align="right" />
</a>

[![Build Status](https://travis-ci.org/xxtea/xxtea-php.svg?branch=master)](https://travis-ci.org/xxtea/xxtea-php)
[![Packagist](https://img.shields.io/packagist/v/xxtea/xxtea.svg)](https://packagist.org/packages/xxtea/xxtea)
[![Packagist Download](https://img.shields.io/packagist/dm/xxtea/xxtea.svg)](https://packagist.org/packages/xxtea/xxtea)
[![License](https://img.shields.io/packagist/l/xxtea/xxtea.svg)](https://packagist.org/packages/xxtea/xxtea)

## 简介

XXTEA 是一个快速安全的加密算法。本项目是 XXTEA 加密算法的 PHP 实现。

它不同于原始的 XXTEA 加密算法。它是针对字符串进行加密的，而不是针对 uint32 数组。同样，密钥也是字符串。

## 安装

下载 xxtea.php，然后把它放在你的开发目录下就行了。

## 使用

```php
<?php
    require_once("xxtea.php");
    $str = "Hello World! 你好，中国！";
    $key = "1234567890";
    $encrypt_data = xxtea_encrypt($str, $key);
    $decrypt_data = xxtea_decrypt($encrypt_data, $key);
    if ($str == $decrypt_data) {
        echo "success!";
    } else {
        echo "fail!";
    }
?>
```
