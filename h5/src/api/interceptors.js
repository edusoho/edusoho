import axios from 'axios';
import store from '@/store';
import router from '@/router';
import * as types from '@/store/mutation-types';

// 状态码
const statusCode = {
  EXPIRED_CREDENTIAL: 5
};

axios.interceptors.request.use(config => {
  if (config.name.indexOf('Live') === -1) {
    config.headers.Accept = 'application/vnd.edusoho.v2+json';
  }
  config.headers.SessionIgnore = 1;

  if (store.state.token) {
    config.headers['X-Auth-Token'] = store.state.token;
  }
  store.commit('UPDATE_LOADING_STATUS', true);

  return config;
}, error => Promise.reject(error));

axios.interceptors.response.use(res => {
  store.commit('UPDATE_LOADING_STATUS', false);
  return res.data;
}, error => {
  store.commit('UPDATE_LOADING_STATUS', false);

  switch (error.response.status) {
    case 401:
      const code = error.response.data.error.code;
      // token过期的情况
      if (code === statusCode.EXPIRED_CREDENTIAL) {
        store.commit(types.USER_LOGOUT);

        router.replace({
          name: 'login',
          query: { redirect: router.currentRoute.name }
        });
      }
      break;
    default:
      break;
  }

  return Promise.reject(error.response.data.error);
});
