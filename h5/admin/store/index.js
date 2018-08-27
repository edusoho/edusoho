import Vuex from 'vuex';
import Vue from 'vue';

import * as getters from './getters';
import * as actions from './actions';
import mutations from './mutations';

Vue.use(Vuex);

const state = {
  isLoading: false,
  categories: [],
  user: {},
  csrfToken: '',
};


export default new Vuex.Store({
  state,
  getters,
  actions,
  mutations
});
