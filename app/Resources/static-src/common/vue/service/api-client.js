import axios from 'axios';
import Vue from 'common/vue';
import { loginAgain } from 'common/ajaxError';

const apiStore = {
  token: '',
  user: null,

  setAuth(token, user) {
    localStorage.setItem('auth_token', token);
    localStorage.setItem('auth_user', JSON.stringify(user));
    this.token = token;
    this.user = user;
  },

  clearAuth() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
    this.token = '';
    this.user = null;
  },

  initAuth() {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('auth_user'));
    if (token && user) {
      this.token = token;
      this.user = user;
    } else {
      this.token = '';
      this.user = null;
    }
  }
};

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
      if (![4042701].includes(error.response.data.error.code)) {
        Vue.prototype.$message.error(error.response.data.error.message)
      }
    } catch (e) {

    }

    return Promise.reject(error);
});

export { apiClient, apiStore };
