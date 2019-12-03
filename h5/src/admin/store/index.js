import Vuex from 'vuex';
import Vue from 'vue';

import * as actions from './actions';
import mutations from './mutations';

Vue.use(Vuex);

const state = {
  isLoading: false,
  courseCategories: [],
  classCategories: [],
  user: {},
  csrfToken: '',
  draft: {},
  vipLevels: [],
  vipSettings: {},
  vipSetupStatus: false,
  vipPlugin: {},
  courseSettings: {},
  classroomSettings: {},
  settings: {}
};

export default new Vuex.Store({
  state,
  actions,
  mutations
});
