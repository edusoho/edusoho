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


export const getUserInfo = ({ commit }) => Api.getUserInfo({})
  .then(res => {
    commit(types.USER_INFO, res);
    return res;
  });

export const addUser = ({ commit }, data) =>
  new Promise((resolve, reject) => {
    Api.addUser({
      data
    }).then(res => {
      commit(types.ADD_USER, res);
      resolve(res);
      return res;
    }).catch(err => reject(err));
  });

export const sendSmsCenter = ({ commit }, data) =>
  new Promise((resolve, reject) => {
    Api.getSmsCenter({
      data
    }).then(res => {
      commit(types.SMS_CENTER);
      resolve(res);
      return res;
    }).catch(err => reject(err));
  });

export const setNickname = ({ commit }, { nickname }) =>
  new Promise((resolve, reject) => {
    Api.setNickname({
      data: {
        nickname
      }
    }).then(res => {
      commit(types.SET_NICKNAME, res);
      resolve(res);
      return res;
    }).catch(err => reject(err));
  });

export const setAvatar = ({ commit }, { avatarId }) =>
  new Promise((resolve, reject) => {
    Api.setAvatar({
      data: {
        avatarId
      }
    }).then(res => {
      commit(types.SET_NICKNAME, res);
      resolve(res);
      return res;
    }).catch(err => reject(err));
  });

// 全局设置
export const getGlobalSettings = ({ commit }, { type, key }) =>
  new Promise((resolve, reject) => {
    Api.getSettings({
      query: {
        type
      }
    }).then(res => {
      document.title = res.name;
      commit(types.GET_SETTINGS, {
        key,
        setting: res
      });
      resolve(res);
      return res;
    }).catch(err => reject(err));
  });
