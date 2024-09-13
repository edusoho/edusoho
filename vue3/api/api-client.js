import axios from 'axios';
import {message} from 'ant-design-vue';
import { loginAgain } from 'common/ajaxError';

const apiClient = axios.create({
  baseURL: '/api',
  timeout: 15000,
});

const csrfToken = document.getElementsByTagName('meta')['csrf-token'];
if (csrfToken) {
  localStorage.setItem('csrf-token', csrfToken.content);
}

apiClient.interceptors.request.use(
  config => {
    config.headers['X-Requested-With'] = 'XMLHttpRequest';
    config.headers['X-CSRF-Token'] = localStorage.getItem('csrf-token');
    config.headers.Accept = 'application/vnd.edusoho.v2+json';

    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

apiClient.interceptors.response.use(
  (response) => {
    return response.data;
  },
  (error) => {
    if ([401].includes(error.response.status)) {
      loginAgain();

      return;
    }

    try {
      if (![].includes(error.response.data.error.code)) {
        message.error(error.response.data.error.message);
      }
    } catch (e) {
      console.log(e);
    }

    return Promise.reject(error);
  }
);

export { apiClient };
