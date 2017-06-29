/**
 *     __  ___
 *    /  |/  /___   _____ _____ ___   ____   ____ _ ___   _____
 *   / /|_/ // _ \ / ___// ___// _ \ / __ \ / __ `// _ \ / ___/
 *  / /  / //  __/(__  )(__  )/  __// / / // /_/ //  __// /
 * /_/  /_/ \___//____//____/ \___//_/ /_/ \__, / \___//_/
 *                                        /____/
 *
 * @description MessengerJS, a common cross-document communicate solution.
 * @author biqing kwok
 * @version 2.0
 * @license release under MIT license
 */
define(function(require, exports, module) {
  module.exports = (function () {

    // 消息前缀, 建议使用自己的项目名, 避免多项目之间的冲突
    // !注意 消息前缀应使用字符串类型
    var prefix = "[PROJECT_NAME]",
      supportPostMessage = 'postMessage' in window;

    // Target 类, 消息对象
    function Target(target, name, prefix) {
      var errMsg = '';
      if (arguments.length < 2) {
        errMsg = 'target error - target and name are both required';
      } else if (typeof target != 'object') {
        errMsg = 'target error - target itself must be window object';
      } else if (typeof name != 'string') {
        errMsg = 'target error - target name must be string type';
      }
      if (errMsg) {
        throw new Error(errMsg);
      }
      this.target = target;
      this.name = name;
      this.prefix = prefix;
    }

    // 往 target 发送消息, 出于安全考虑, 发送消息会带上前缀
    if (supportPostMessage) {
      // IE8+ 以及现代浏览器支持
      Target.prototype.send = function (msg) {
        this.target.postMessage(this.prefix + '|' + this.name + '__Messenger__' + msg, '*');
      };
    } else {
      // 兼容IE 6/7
      Target.prototype.send = function (msg) {
        var targetFunc = window.navigator[this.prefix + this.name];
        if (typeof targetFunc == 'function') {
          targetFunc(this.prefix + msg, window);
        } else {
          throw new Error("target callback function is not defined");
        }
      };
    }

    // 信使类
    // 创建Messenger实例时指定, 必须指定Messenger的名字, (可选)指定项目名, 以避免Mashup类应用中的冲突
    // !注意: 父子页面中projectName必须保持一致, 否则无法匹配
    function Messenger(messengerName, projectName) {
      this.targets = {};
      this.name = messengerName;
      this.listenFunc = [];
      this.prefix = projectName || prefix;
      this.initListen();
    }

    // 添加一个消息对象
    Messenger.prototype.addTarget = function (target, name) {
      var targetObj = new Target(target, name, this.prefix);
      this.targets[name] = targetObj;
    };

    // 初始化消息监听
    Messenger.prototype.initListen = function () {
      var self = this;
      var generalCallback = function (msg) {
        if (typeof msg == 'object' && msg.data) {
          msg = msg.data;
        }

        try {
          var msgPairs = msg.split('__Messenger__');
        } catch (e) {
          return true;
        }

        var msg = msgPairs[1];
        var pairs = msgPairs[0].split('|');
        var prefix = pairs[0];
        var name = pairs[1];

        for (var i = 0; i < self.listenFunc.length; i++) {
          if (prefix + name === self.prefix + self.name) {
            self.listenFunc[i](msg);
          }
        }
      };

      if (supportPostMessage) {
        if ('addEventListener' in document) {
          window.addEventListener('message', generalCallback, false);
        } else if ('attachEvent' in document) {
          window.attachEvent('onmessage', generalCallback);
        }
      } else {
        // 兼容IE 6/7
        window.navigator[this.prefix + this.name] = generalCallback;
      }
    };

    // 监听消息
    Messenger.prototype.listen = function (callback) {
      var i = 0;
      var len = this.listenFunc.length;
      var cbIsExist = false;
      for (; i < len; i++) {
        if (this.listenFunc[i] == callback) {
          cbIsExist = true;
          break;
        }
      }
      if (!cbIsExist) {
        this.listenFunc.push(callback);
      }
    };
    // 注销监听
    Messenger.prototype.clear = function () {
      this.listenFunc = [];
    };
    // 广播消息
    Messenger.prototype.send = function (msg) {
      var targets = this.targets,
        target;
      for (target in targets) {
        if (targets.hasOwnProperty(target)) {
          targets[target].send(msg);
        }
      }
    };

    return Messenger;
  })();

});