(function () {
<<<<<<< HEAD
  "use strict"
  function postNativeMessage(params) {
    console.log(params);
    params = params || {};
    if (params === "undefind") return;
    if (params.action === "undefind") return;
    delete params.data.metas;
    var data = {
      action: params.action,
      callback: "nativeCallback",
      data: params.data,
    }
    var dataStr = JSON.stringify(data);
=======
  function postNativeMessage(params) {
    console.log(params);
    params = params || {};
    if (params === 'undefind') return;
    if (params.action === 'undefind') return;
    delete params.data.metas;
    const data = {
      action: params.action,
      callback: 'nativeCallback',
      data: params.data
    };
    const dataStr = JSON.stringify(data);
>>>>>>> master
    window.native && window.native.sendMessage(dataStr || null);
    window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.native && window.webkit.messageHandlers.native.postMessage(dataStr || null);
  }
  window.postNativeMessage = postNativeMessage;
  window.nativeCallback = function (data) {
    console.log(data.nickname);
<<<<<<< HEAD
  }
})()
=======
  };
}());
>>>>>>> master
