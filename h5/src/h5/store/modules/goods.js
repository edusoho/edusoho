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
  show_review: 0,
  show_classroom_review: 0,
  show_course_review: 0,
  show_question_bank_review: 0,
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
