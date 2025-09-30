import gwm from 'gwm';
import Api from '@/api';

let watermarkInited = false;

const getTaskWatermark = async () => {
  return Api.getWatermark({
    query: {name: 'task'},
  });
};

const initTaskWatermark = async () => {
  if (watermarkInited) {
    return;
  }
  const watermark = await getTaskWatermark();
  if (!watermark.text) {
    return;
  }
  gwm.creation({
    txt: watermark.text,
    color: watermark.color,
    alpha: watermark.alpha,
    mode: 'svg',
    watch: false,
    fontSize: 12,
    angle: -15,
    width: 200,
    height: 150,
    font: 'sans-serif',
    destroy: false,
    css: {
      'z-index': 99999,
      'pointer-events': 'none',
    },
  });
  watermarkInited = true;
};

const destroyWatermark = () => {
  watermarkInited = false;
  gwm.gwmDom && gwm.gwmDom.remove();
};

export {getTaskWatermark, initTaskWatermark, destroyWatermark};
