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

export const setMobile = ({ commit }, { query, data }) =>
  new Promise((resolve, reject) => {
    Api.setMobile({ query, data }).then(res => {
      commit(types.BIND_MOBILE, res);
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
  Api.getSettings({
    query: {
      type
    }
  }).then(res => {
    if (type === 'site') {
      document.title = res.name;
    }
    commit(types.GET_SETTINGS, {
      key,
      setting: res || {}
    });
    return res;
  });

// 全局vip元素显示开关
export const setVipSwitch = ({ commit }, isOn) =>
  new Promise(resolve => {
    if (!isOn) {
      commit(types.GET_SETTINGS, { key: 'vipSwitch', setting: isOn });
      resolve(isOn);
      return isOn;
    }
    return Api.getVipLevels().then(levels => {
      const levelsExist = !!(levels && levels.length);
      commit(types.GET_SETTINGS, { key: 'vipSwitch', setting: levelsExist });
      resolve(levelsExist);
      return levelsExist;
    });
  });

// 全局公众号显示开关
export const setWeChatSwitch = ({ commit }, isOn) =>
  new Promise(resolve => {
    if (!isOn) {
      commit(types.GET_SETTINGS, { key: 'wechatSwitch', setting: isOn });
      resolve(isOn);
      return isOn;
    }
    return Api.weChatNotifyState().then(res => {
      const isWeChatBind = !!(res && res.bind);
      commit(types.GET_SETTINGS, { key: 'wechatSwitch', setting: !isWeChatBind });
      resolve(isWeChatBind);
      return isWeChatBind;
    }).catch(error => {
      console.log(error.message);
    });
  });
