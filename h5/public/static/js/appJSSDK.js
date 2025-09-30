(function () {
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
    window.native && window.native.sendMessage(dataStr || null);
    window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.native && window.webkit.messageHandlers.native.postMessage(dataStr || null);
  }
  window.postNativeMessage = postNativeMessage;
  window.nativeCallback = function (data) {
    console.log(data.nickname);
  };
}());
