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
  }
};
