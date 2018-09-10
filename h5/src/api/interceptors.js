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
      if (code === statusCode.EXPIRED_CREDENTIAL) { // 待解决：错误码没有同意，这种判断方式需要之后接口错误码同一之后才可用
        store.commit(types.USER_LOGOUT);

        router.replace({ // 待解决：replace 会导致返回按钮的功能有问题
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
