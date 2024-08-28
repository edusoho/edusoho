import axios from 'axios';
import {message} from 'ant-design-vue';
import { loginAgain } from 'common/ajaxError';

const apiClient = axios.create({
  timeout: 15000
});

let csrfToken = document.getElementsByTagName('meta')['csrf-token'];
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

const unLoginStatus = [401]

apiClient.interceptors.response.use(
  (response) => {
    return response.data;
  },
  (error) => {
    if (unLoginStatus.includes(error.response.status)) {
      loginAgain()

      return
    }

    try {
      if (![].includes(error.response.data.error.code)) {
        message.error(error.response.data.error.message);
        if (![5005001].includes(error.response.data.error.code)) {
          return Promise.reject(error);
        }
      }
    } catch (e) {
      return Promise.reject(error);
    }

    return Promise.resolve(error);
  });

export { apiClient };
