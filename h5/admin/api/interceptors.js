import axios from 'axios';
import store from '@admin/store';
import router from '@admin/router';
import * as types from '@admin/store/mutation-types';

// 状态码
const statusCode = {
  EXPIRED_CREDENTIAL: 5
};

axios.interceptors.request.use(config => {
  config.headers.Accept = 'application/vnd.edusoho.v2+json';

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

      break;
    default:
      break;
  }

  return Promise.reject(error.response.data.error);
});
