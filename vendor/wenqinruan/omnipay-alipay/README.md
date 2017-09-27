# Omnipay: Alipay

[![travis][ico-travis]][link-travis]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Software License][ico-license]](LICENSE)
[![Donate][ico-donate-paypal]][link-donate-paypal]
[![Donate][ico-donate]][link-donate]


**Alipay driver for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/omnipay/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP. This package implements Alipay support for Omnipay.

> Cross-border Alipay payment please use [`lokielse/omnipay-global-alipay`](https://github.com/lokielse/omnipay-global-alipay)
 
> Legacy Version please use [`"lokielse/omnipay-alipay": "dev-legacy"`](https://github.com/lokielse/omnipay-alipay/tree/legacy)

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

    "lokielse/omnipay-alipay": "^2.0",

And run composer to update your dependencies:

    $ composer update -vvv

## Basic Usage

The following gateways are provided by this package:

| Gateway       	    		|         Description             |说明                 | Links |
|:---------------	    	|:---------------------------     |:---------         |:----------:|
| Alipay_AopPage 	    		| Alipay Page Gateway             |电脑网站支付 - new    | [Usage][link-wiki-aop-page] [Doc][link-doc-aop-page] |
| Alipay_AopApp 	    		| Alipay APP Gateway              |APP支付 - new    | [Usage][link-wiki-aop-app] [Doc][link-doc-aop-app] |
| Alipay_AopF2F 	    		| Alipay Face To Face Gateway     |当面付 - new         | [Usage][link-wiki-aop-f2f] [Doc][link-doc-aop-f2f] |
| Alipay_AopWap 	    		| Alipay WAP Gateway              |手机网站支付 - new     | [Usage][link-wiki-aop-wap] [Doc][link-doc-aop-wap] |
| Alipay_LegacyApp 	    	| Alipay Legacy APP Gateway       |APP支付      | [Usage][link-wiki-legacy-app] [Doc][link-doc-legacy-app]      |
| Alipay_LegacyExpress 		| Alipay Legacy Express Gateway   |即时到账    | [Usage][link-wiki-legacy-express] [Doc][link-doc-legacy-express]|
| Alipay_LegacyWap      	| Alipay Legacy WAP Gateway   |手机网站支付     | [Usage][link-wiki-legacy-wap] [Doc][link-doc-legacy-wap]       |

## Usage

### Purchase (购买)

```php
/**
 * @var AopAppGateway $gateway
 */
$gateway = Omnipay::create('Alipay_AopPage');
$gateway->setSignType('RSA2'); // RSA/RSA2/MD5
$gateway->setAppId('the_app_id');
$gateway->setPrivateKey('the_app_private_key');
$gateway->setAlipayPublicKey('the_alipay_public_key');
$gateway->setReturnUrl('https://www.example.com/return');
$gateway->setNotifyUrl('https://www.example.com/notify');

/**
 * @var AopTradePagePayResponse $response
 */
$response = $gateway->purchase()->setBizContent([
    'subject'      => 'test',
    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
    'total_amount' => '0.01',
    'product_code' => 'FAST_INSTANT_TRADE_PAY',
])->send();

$url = $response->getRedirectUrl();
```

For general usage instructions, please see the main [Omnipay](https://github.com/omnipay/omnipay)
repository.

## Related

- [Laravel-Omnipay](https://github.com/ignited/laravel-omnipay)
- [Omnipay-GlobalAlipay](https://github.com/lokielse/omnipay-global-alipay)
- [Omnipay-WechatPay](https://github.com/lokielse/omnipay-wechatpay)
- [Omnipay-UnionPay](https://github.com/lokielse/omnipay-unionpay)

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/lokielse/omnipay-alipay/issues),
or better yet, fork the library and submit a pull request.

[ico-version]: https://img.shields.io/packagist/v/lokielse/omnipay-alipay.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-travis]: https://img.shields.io/travis/lokielse/omnipay-alipay/master.svg
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/lokielse/omnipay-alipay.svg
[ico-code-coverage]: https://img.shields.io/codecov/c/github/lokielse/omnipay-alipay/master.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/lokielse/omnipay-alipay.svg
[ico-downloads]: https://img.shields.io/packagist/dt/lokielse/omnipay-alipay.svg
[ico-donate]: https://img.shields.io/badge/-%E7%BA%A2%E5%8C%85-red.svg?logo=data%3Aimage%2Fpng%3Bbase64%2CiVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8%2F9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw%2FeHBhY2tldCBiZWdpbj0i77u%2FIiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8%2BIDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoTWFjaW50b3NoKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDowOTE0NEQ3OTdBREYxMUU2QkVGRkQ0ODM3M0M3RTcwNCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDowOTE0NEQ3QTdBREYxMUU2QkVGRkQ0ODM3M0M3RTcwNCI%2BIDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjA5MTQ0RDc3N0FERjExRTZCRUZGRDQ4MzczQzdFNzA0IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjA5MTQ0RDc4N0FERjExRTZCRUZGRDQ4MzczQzdFNzA0Ii8%2BIDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY%2BIDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8%2BXf%2Fy5QAAAqpJREFUeNp0Uz1vE0EQfbt7n7Zjx%2BAkVoQpEJ%2BiSIWQKOmCoEkH4k8gCpAokJCoaOkpEVLS8R%2BgCIJIJCiCyBEJJLFDEp%2FtO99574bZM4EExEh7Z83tvDfvzVgQEdKlxZvxwsvHtLN1iYZJEVkGZCmQ8pv4HIYQgOOEsj696s7deaquXFsQ%2BsPijej5s3kMIh%2F8HcMhDAAZEE2jQktwrRj9ZkLSfMdxh%2F69R7fVw6mxeWY%2BlTMmyehCShBKwmpUIKseqKc5l3HOgDCYUkyUKHz7OmPh%2B8YFmWomHbVKGUFWXPi3GpDjUZ7LDiYRvd4AdQY5sORutO1gf715Rt2dKD9JMxI2o5ojMwF39jRUPQBUiY8D4QWwajUky%2FsI0yHaYUQ%2FwgEJkLLGPU8EccwJZmPJXsHB2ZoW6IdApcoAFhD0kJ100Yx7pJIMFd8T9VKRm2GZJddBxXNFxtoHQ41eMkQcdFCoss5wF%2BBLsCTSIEDDLwpvzGIeYsk8PZYt8zEa1%2Fnt2RYmXB%2Fdtz3Sts2F7IvS0Eyy%2F6ZLdibzu6b4MCwcCQNCPDK%2FmYrdV3skLrpsGE%2Fz0wCFbRLCUfmdo3EMwIQpiESKlXctca5ZxWanC8EqZhp1ZH8V%2FwdAos0GrgUHiEijGyeI0xQBu3%2B1Mc12yGNd%2FANgRmG2zmc%2FOoPYDAYOj7fPS%2FZ7G%2FO1Hj2kJkqOAwiEPAnNm1d0HLiWYlaBsufCVX88sKXCZtDVklv8kicNuhh1UGbXDRuPF5pd9yyLer%2BkmNk7DLrRCfB%2Bq7WuZqul1mTBm%2BP1lBxQ7IHhMNrP107knaREoux5mCoV0ec%2F2%2FJOG0tbLQN%2BX10v%2Byu8hZ9Z72U%2B1W4cS%2BP21FgRkluq%2BC6ZZTPaVnf38HG7rbe7%2FTUhxQNu%2BMVPAQYAqNVYlao%2BUU8AAAAASUVORK5CYII%3D
[ico-donate-paypal]: https://img.shields.io/badge/%F0%9F%8D%BC-donate-ff69b4.svg

[link-packagist]: https://packagist.org/packages/lokielse/omnipay-alipay
[link-travis]: https://travis-ci.org/lokielse/omnipay-alipay
[link-scrutinizer]: https://scrutinizer-ci.com/g/lokielse/omnipay-alipay/code-structure
[link-code-coverage]: https://codecov.io/github/lokielse/omnipay-alipay?branch=master
[link-code-quality]: https://scrutinizer-ci.com/g/lokielse/omnipay-alipay
[link-downloads]: https://packagist.org/packages/lokielse/omnipay-alipay
[link-author]: https://github.com/lokielse
[link-contributors]: ../../contributors
[link-donate]: https://cloud.githubusercontent.com/assets/1573211/18808259/a283d596-828f-11e6-8810-4a2e16d5e319.jpg
[link-donate-paypal]: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=lokielse%40gmail%2ecom&lc=US&item_name=Omnipay%20Alipay&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted

[link-wiki-aop-page]: https://github.com/lokielse/omnipay-alipay/wiki/Aop-Page-Gateway
[link-wiki-aop-app]: https://github.com/lokielse/omnipay-alipay/wiki/Aop-APP-Gateway
[link-wiki-aop-f2f]: https://github.com/lokielse/omnipay-alipay/wiki/Aop-Face-To-Face-Gateway
[link-wiki-aop-wap]: https://github.com/lokielse/omnipay-alipay/wiki/Aop-WAP-Gateway
[link-wiki-legacy-app]: https://github.com/lokielse/omnipay-alipay/wiki/Legacy-APP-Gateway
[link-wiki-legacy-express]: https://github.com/lokielse/omnipay-alipay/wiki/Legacy-Express-Gateway
[link-wiki-legacy-wap]: https://github.com/lokielse/omnipay-alipay/wiki/Legacy-WAP-Gateway
[link-doc-aop-page]: https://doc.open.alipay.com/doc2/detail.htm?treeId=270&articleId=105901&docType=1
[link-doc-aop-app]: https://doc.open.alipay.com/docs/doc.htm?treeId=204&articleId=105051&docType=1
[link-doc-aop-f2f]: https://doc.open.alipay.com/docs/doc.htm?treeId=194&articleId=105072&docType=1
[link-doc-aop-wap]: https://doc.open.alipay.com/docs/doc.htm?treeId=203&articleId=105288&docType=1
[link-doc-legacy-app]: https://doc.open.alipay.com/doc2/detail?treeId=59&articleId=103563&docType=1
[link-doc-legacy-express]: https://doc.open.alipay.com/docs/doc.htm?treeId=108&articleId=103950&docType=1
[link-doc-legacy-wap]: https://doc.open.alipay.com/docs/doc.htm?treeId=60&articleId=103564&docType=1