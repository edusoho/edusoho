# XXTEA for PHP

<a href="https://github.com/xxtea/">
    <img src="https://avatars1.githubusercontent.com/u/6683159?v=3&s=86" alt="XXTEA logo" title="XXTEA" align="right" />
</a>

[![Build Status](https://travis-ci.org/xxtea/xxtea-php.svg?branch=master)](https://travis-ci.org/xxtea/xxtea-php)
[![Packagist](https://img.shields.io/packagist/v/xxtea/xxtea.svg)](https://packagist.org/packages/xxtea/xxtea)
[![Packagist Download](https://img.shields.io/packagist/dm/xxtea/xxtea.svg)](https://packagist.org/packages/xxtea/xxtea)
[![License](https://img.shields.io/packagist/l/xxtea/xxtea.svg)](https://packagist.org/packages/xxtea/xxtea)

## Introduction

XXTEA is a fast and secure encryption algorithm. This is a XXTEA library for PHP.

It is different from the original XXTEA encryption algorithm. It encrypts and decrypts string instead of uint32 array, and the key is also string.

## Installation

Download the xxtea.php, and put it in your develepment directory.

## Usage

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
