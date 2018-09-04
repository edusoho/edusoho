import DocPlayer from 'app/common/doc-player';

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
  new DocPlayer({
    element: $element,
    swfUrl: $element.data('swf'),
    pdfUrl: $element.data('pdf'),
    watermarkOptions: {
      contents,
      xPosition: 'center',
      yPosition: 'center',
      rotate: 45,
    },
    canCopy: $element.data('disableCopy')
  });
}
