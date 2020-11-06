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
  settings: {},
  createMarketingUrl: '/admin/v2/login/marketing?target=activity_create',
};

export default new Vuex.Store({
  state,
  actions,
  mutations,
});
