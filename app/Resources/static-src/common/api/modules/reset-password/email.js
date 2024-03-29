import ajax from '../../ajax';

const resetEmail = (api) => {
  return {
    patch(options) {
      return ajax(Object.assign({
        url: `${api}/user/${options.params.email}/password/email`,
        type: 'PUT',
      }, options));
    },
  };
};

export default resetEmail;