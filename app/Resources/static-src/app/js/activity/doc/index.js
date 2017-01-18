import DocPlayer from '../../../common/doc-player';
import ActivityEmitter from '../activity-emitter';
let $element = $('#document-content');
let watermarkUrl = $element.data('watermark-url');

console.log(watermarkUrl);

if(watermarkUrl) {
  console.log('watermarkUrl');
  $.get(watermarkUrl, function(watermark) {
    console.log(watermark);
    initDocPlayer(watermark);
  });
}else {
  initDocPlayer('');
}

function initDocPlayer(contents) {
  let doc = new DocPlayer({
    element: $element,
    swfUrl: $element.data('swf'),
    pdfUrl: $element.data('pdf'),
    watermarkOptions: {
      contents,
      xPosition: 'center',
      yPosition: 'center',
      rotate: 45,
    }
  });
}





let activityEmitter = new ActivityEmitter();

let finishType = $element.data('finishType');

if (finishType == 'time') {
  let finishDetail = $element.data('finishDetail');
  activityEmitter.receive('doing', (data) => {
    if (finishDetail <= data.learnedTime) {
      activityEmitter.emit('finish');
    }
  });
}
