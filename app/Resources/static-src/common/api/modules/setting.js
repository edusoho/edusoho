import ajax from '../ajax';

const setting = (api) => {
  return {
    get(name) {
      return ajax({
        url: `${api}/setting/${name}`,
      });
    },
  };
};

export default setting;
