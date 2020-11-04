import * as types from '../mutation-types';

const state = {
  selectedSpecs: {},
  tasks: [],
  price: 0,
  coinPrice: 0,
  courseLessons: [],
  OptimizationCourseLessons: [],
  allTask: {},
  taskStatus: '',
};

const mutations = {
  [types.GET_COURSE_LESSONS](currentState, payload) {},
};

const actions = {};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
