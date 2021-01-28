import ajax from '../ajax';

const newNotificationModule = (api) => {
  return {
    search(options) {
      return ajax(Object.assign({
        url: `${api}/newNotifications`,
        type: 'GET',
      }, options));
    }
  };
};

export default newNotificationModule;