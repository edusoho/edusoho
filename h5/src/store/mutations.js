import * as types from './mutation-types';

export default {
  [types.UPDATE_LOADING_STATUS](state, payload) {
    state.isLoading = payload;
  },
  [types.USER_LOGIN](state, payload) {
    state.token = payload.token;
    state.user = payload.user;
    localStorage.setItem('token', payload.token);
    localStorage.setItem('user', JSON.stringify(payload.user));
  },
  [types.USER_LOGOUT](state) {
    state.token = null;
    state.user = {};
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  },
  [types.USER_INFO](state, payload) {
    state.user = payload;
    localStorage.setItem('user', JSON.stringify(payload));
  },
  [types.ADD_USER](state, payload) {
    state.user = payload;
    localStorage.setItem('user', JSON.stringify(payload));
  },
  [types.SMS_CENTER](state, payload) {
    state.smsToken = payload;
  },
  [types.SET_NICKNAME](state, payload) {
    state.user = Object.assign({}, state.user, {
      nickname: payload.nickname
    });
    localStorage.setItem('user', JSON.stringify(payload));
  },
  [types.SET_AVATAR](state, payload) {
    state.user = payload;
    localStorage.setItem('user', JSON.stringify(payload));
  },
  [types.GET_SETTINGS](state, { key, setting }) {
    state[key] = setting;
  },
  [types.SET_NAVBAR_TITLE](state, payload) {
    state.title = payload;
  }
};
