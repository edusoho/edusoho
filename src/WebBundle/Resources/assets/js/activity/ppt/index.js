import PptPlayer from '../../../common/ppt-player';
import ActivityEmitter from "../activity-emitter";

let watermarkUrl = $('#activity-ppt-content').data('watermarkUrl');
let emitter = new ActivityEmitter();

let createPPT = (watermark) => {
  let ppt = new PptPlayer({
    element: '#activity-ppt-content',
    slides: $('#activity-ppt-content').data('slides').split(','),
    watermark: watermark
  });

  return ppt.once('end', () => {
    console.log('end');
    emitter.emit('finish').then(() => {
      console.log('ppt.finish');
    }).catch((error) => {
      console.error(error);
    });
  });
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
