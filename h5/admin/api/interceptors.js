import axios from 'axios';
import store from 'admin/store';

// 状态码
// const statusCode = {
//   EXPIRED_CREDENTIAL: 5
// };

axios.interceptors.request.use(config => {
  config.headers.Accept = 'application/vnd.edusoho.v2+json';

  const env = process.env.NODE_ENV;

  if (env !== 'production') {
    config.headers['X-Auth-Token'] = 'ovmf3z0xtu8c0ggok4gwgksg0gk488g';
  } else {
    config.headers['X-Requested-With'] = 'XMLHttpRequest';
    config.headers['X-CSRF-Token'] = store.state.csrfToken;
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
      // const code = error.response.data.error.code;

      break;
    default:
      break;
  }

  return Promise.reject(error.response.data.error);
});
