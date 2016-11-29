import DocPlayer from '../../../common/doc-player';
import ActivityEmitter from '../activity-emitter';

let $element = $('#document-content');

let doc = new DocPlayer({
  element: $element,
  swfUrl: $element.data('swf'),
  pdfUrl: $element.data('pdf'),
  watermark: {
    xPosition: 'center',
    yPosition: 'center',
    rotate: 45,
    contents: ''
  }
});

let activityEmitter = new ActivityEmitter();

let finishType = $element.data('finishType');

if(finishType == 'time'){
  let finishDetail = $element.data('finishDetail');
  activityEmitter.receive('doing', (data) => {
    if(finishDetail <= data.learnedTime){
      activityEmitter.emit('finish');
    }
  });
}



