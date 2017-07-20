(function(url) {
    window.QiQiuYun || (window.QiQiuYun = {});
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url + '?' + ~~(Date.now() / 1000 / 60), false); // 可设置缓存时间。当前缓存时间为1分钟。
    xhr.send(null);
    var firstScriptTag = document.getElementsByTagName('script')[0];
    var script = document.createElement('script');
    script.text = xhr.responseText;
    firstScriptTag.parentNode.insertBefore(script, firstScriptTag);
})('//service-cdn.qiqiuyun.net/js-sdk/sdk-v1.js');

let $element = $('#document-content');
let watermarkUrl = $element.data('watermark-url');

if(watermarkUrl) {
  $.get(watermarkUrl, function(watermark) {
    console.log(watermark);
    initDocPlayer(watermark);
  });
}else {
  initDocPlayer('');
}

function initDocPlayer(contents) {
  let doc = new QiQiuYun.Player({
    id: 'document-content',
    resNo: $element.data('resNo'),
    token: $element.data('token')
  });


}
