# CHANGELOG

## [Unreleased]

* 短信服务，增加短信模板相关接口；定义错误码；定义通用模板ID常量。
* 分销服务，完善单元测试。
* XAPI，完善单元测试。


## v0.2.0 (2018-01-27)

* 统一`Service`的构造函数。
* PHP5.3 下无法使用PHP内置的WebServer，导致在 PHP 5.3 下，MockServer 无法运行，所以去除了 MockServer，改用 PHPUnit 的 Mock 来 Mock HTTP Client。
* 去除 `DrpException`，服务端返回错误信息，统一使用 `ResponseException`。
* 云资源播放逻辑从 `RessourceService` 中抽离到 `PlayService`。
* 统一API鉴权签名逻辑到`Auth`。
* 分销API的签名逻辑，改用通用的签名逻辑，不再对BODY截取1024字节做签名。
* 错误码统一声明到 `ErrorCode`。
* XAPIService 构造函数的第二个参数变更，原先：
  ```php
  new XAPIService($auth, array(
      'school' => array(
          'id' => $accessKey,
          'name' => '测试网校'
      )
  ));
  ```
  改为：
  ```php
  new XAPIService($auth, array(
      'school_name' => '测试网校'
    ));
  ```
* 增加工厂类`QiQiuYunSDK`，用于管理所有接口服务实例的创建。