import PptPlayer from '../../../common/ppt-player';
import ActivityEmitter from "../activity-emitter";


let emitter = new ActivityEmitter();
let $content = $('#activity-ppt-content');
let watermarkUrl = $content.data('watermarkUrl');
let finishTime = parseInt($content.data('finishDetail'));

let createPPT = (watermark) => {
  let ppt = new PptPlayer({
    element: '#activity-ppt-content',
    slides: $content.data('slides').split(','),
    watermark: watermark
  });


  if($content.data('finishType') === 'end'){
    ppt.once('end', () => {
      emitter.emit('finish');
    });
  }else{
    emitter.receive('doing', (data) => {
      if(data.learnedTime >= finishTime){
        emitter.emit('finish');
      }
    })
  }

  return ppt;
};

if (watermarkUrl === undefined) {
  let ppt = createPPT();
} else {
  $.get(watermarkUrl)
      .then((watermark) => {
        let ppt = createPPT(watermark);
      })
      .fail(error => {
        console.error(error);
      });
}
