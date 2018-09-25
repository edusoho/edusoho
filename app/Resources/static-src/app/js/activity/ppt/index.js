import PptPlayer from '../../../common/ppt-player';
import ActivityEmitter from '../activity-emitter';


let emitter = new ActivityEmitter();
let $content = $('#activity-ppt-content');
let watermarkUrl = $content.data('watermarkUrl');

let createPPT = (watermark) => {
  let ppt = new PptPlayer({
    element: '#activity-ppt-content',
    slides: $content.data('slides').split(','),
    watermark: watermark
  });


  if ($content.data('finishType') === 'end') {
    if (ppt.total === 1) {
      setTimeout(() => {
        emitter.emit('finish', {page: 1});
      }, 1000);
    } else {
      ppt.once('end', (data) => {
        emitter.emit('finish',data);
      });
    }
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
