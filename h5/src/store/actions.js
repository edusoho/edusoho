import * as types from '@/store/mutation-types';
import Api from '@/api';

export const updateLoading = ({ commit }, { isLoading }) => {
  commit(types.UPDATE_LOADING_STATUS, { isLoading });
};

export const userLogin = ({ commit }, { username, password }) => {
  localStorage.setItem('Authorization', btoa(unescape(encodeURIComponent(`${username}:${password}`))));

  return Api.login({
    headers: {
      Authorization: `Basic ${localStorage.getItem('Authorization')}`
    }
  }).then(res => {
    commit(types.USER_LOGIN, res);
    return res;
  });
};


export const getUserInfo = ({ commit }) => Api.getUserInfo({
  headers: {
    Authorization: `Basic ${localStorage.getItem('Authorization')}`
  }
}).then(res => {
  commit(types.USER_INFO, res);
  return res;
});
