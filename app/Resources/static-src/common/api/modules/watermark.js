import ajax from '../ajax';

const watermark = (api) => {
  return {
    get(scene) {
      return ajax({
        url: `${api}/watermark/${scene}`,
      });
    },
  };
};

export default watermark;
