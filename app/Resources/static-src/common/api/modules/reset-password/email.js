import ajax from '../../ajax';

const resetEmail = (api) => {
  return {
    patch(options) {
      console.log(options);
      return ajax(Object.assign({
        url: `${api}/user/${options.params.email}/password/email`,
        type: 'PATCH',
      }, options));
    },
  };
};

export default resetEmail;