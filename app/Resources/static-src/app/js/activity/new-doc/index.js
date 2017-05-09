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
  let doc = new DocPlayerSDK({
    id: 'document-content',
    src: $element.data('html'),
    key: $element.data('encryptKey'),
    iv: $element.data('iv')
  });


}
