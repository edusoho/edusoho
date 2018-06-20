import ajax from '../../ajax';

const resetEmail = (api) => {
  return {
    patch(options) {
      console.log(options);
      return ajax(Object.assign({
        url: `${api}/user/${options.email}/password/email?token${options.token}`,
        type: 'patch',
      }, options));
    },
  };
};

export default resetEmail;