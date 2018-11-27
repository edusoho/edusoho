import * as types from './mutation-types';

export default {
  [types.UPDATE_LOADING_STATUS](state, payload) {
    state.isLoading = payload;
  },
  [types.GET_COURSE_CATEGORIES](state, payload) {
    state.categories = payload;
  },
  [types.GET_CLASS_CATEGORIES](state, payload) {
    state.categories = payload;
  },
  [types.GET_CSRF_TOKEN](state, payload) {
    state.csrfToken = payload;
  },
  [types.UPDATE_DRAFT](state, payload) {
    state.draft = payload;
  },
};
