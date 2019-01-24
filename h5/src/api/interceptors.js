import axios from 'axios';
import store from '@/store';
import router from '@/router';
import * as types from '@/store/mutation-types';

// 状态码
const statusCode = {
  EXPIRED_CREDENTIAL: 5,
  TOKEN_NOT_EXIST: 4040117
};

axios.interceptors.request.use(config => {
  if (config.interceptor === 'end') {
    return config;
  }
  if (config.name.indexOf('Live') === -1) {
    config.headers.Accept = 'application/vnd.edusoho.v2+json';
  }
  config.headers.SessionIgnore = 1;

  if (store.state.token) {
    config.headers['X-Auth-Token'] = store.state.token;
  }

  // 自定义配置显示 loading 动画
  if (config.disableLoading) {
    return config;
  }

  store.commit('UPDATE_LOADING_STATUS', true);

  return config;
}, error => Promise.reject(error));

axios.interceptors.response.use(res => {
  if (res.data.hash) {
    return res;
  }
  // 自定义配置显示 loading 动画
  if (res.config.disableLoading) {
    return res.data;
  }
  store.commit('UPDATE_LOADING_STATUS', false);
  return res.data;
}, error => {
  store.commit('UPDATE_LOADING_STATUS', false);

  let code = '';
  switch (error.response.status) {
    case 401:
    case 404:
      code = error.response.data.error.code;
      // 待解决：错误码没有统一，这种判断方式需要之后接口错误码统一之后才可用
      // token过期的情况
      if (code === statusCode.EXPIRED_CREDENTIAL || code === statusCode.TOKEN_NOT_EXIST) {
        store.commit(types.USER_LOGOUT);
        router.replace({ // 待解决：replace 会导致返回按钮的功能有问题
          name: 'prelogin',
          query: { redirect: router.currentRoute.fullPath }
        }, () => {
          window.location.reload(); // redirect 为 '/' 时，需要刷新才能进入对应页面的问题
        });
      }
      break;
    default:
      break;
  }

  return Promise.reject(error.response.data.error);
});
