import ajax from '../../ajax';

const resetMobile = (api) => {
  return {
    patch(options) {
      console.log(options);
      return ajax(Object.assign({
        url: `${api}/user/${options.params.mobile}/password/mobile`,
        type: 'PUT',
      }, options));
    },
  };
};

export default resetMobile;