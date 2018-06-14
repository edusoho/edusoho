import axios from 'axios';
import store from '@/store';
import router from '@/router';
import * as types from '@/store/mutation-types';

// 状态码
const statusCode = {
  EXPIRED_CREDENTIAL: 5
};

axios.interceptors.request.use(config => {
  config.headers.Accept = 'application/vnd.edusoho.v2+json';

  if (store.state.token) {
    config.headers['X-CSRF-Token'] = store.state.token;
  }

  return config;
}, error => Promise.reject(error));

axios.interceptors.response.use(res => res.data, error => {
  switch (error.response.status) {
    case 401:
      const code = error.response.data.error.code;
      // token过期的情况
      if (code === statusCode.EXPIRED_CREDENTIAL) {
        store.commit(types.USER_LOGOUT);

        router.replace({
          name: 'login',
          query: { redirect: router.name }
        });
      }
      break;
    default:
      break;
  }

  return Promise.reject(error.response.data.error);
});
