import * as types from './mutation-types';

export default {
  [types.UPDATE_LOADING_STATUS](state, payload) {
    state.isLoading = payload;
  },
  [types.GET_CATEGORIES](state, payload) {
    state.categories = payload;
  }
};
