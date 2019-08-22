let url = $('.js-cloud-url').data('url');
(function (url) {
  window.QiQiuYun || (window.QiQiuYun = {});
  var xhr = new XMLHttpRequest();
  xhr.open('GET', url + '?' + ~~(Date.now() / 1000 / 60), false); // 可设置缓存时间。当前缓存时间为1分钟。
  xhr.send(null);
  var firstScriptTag = document.getElementsByTagName('script')[0];
  var script = document.createElement('script');
  script.text = xhr.responseText;
  firstScriptTag.parentNode.insertBefore(script, firstScriptTag);
})(url);

let $element = $('#activity-ppt-content');

initPptPlayer();

function initPptPlayer() {
  new QiQiuYun.Player({
    id: 'activity-ppt-content',
    playServer: app.cloudPlayServer,
    sdkBaseUri: app.cloudSdkBaseUri,
    disableDataUpload: app.cloudDisableLogReport,
    disableSentry: app.cloudDisableLogReport,
    resNo: $element.data('resNo'),
    token: $element.data('token'),
    user: {
      id: $element.data('userId'),
      name: $element.data('userName')
    }
  });
}

