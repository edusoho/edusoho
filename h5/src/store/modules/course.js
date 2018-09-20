import Api from '@/api';
import * as types from '../mutation-types';

const state = {
  selectedPlanId: 0,
  joinStatus: false, // 当前计划是否已加入学习
  sourceType: 'img', //
  details: {},
  taskId: 0, // 任务id
  courseLessons: [] // 课程中所有任务
};

const hasJoinedCourse = course => course.member;

const mutations = {
  [types.GET_COURSE_LESSONS](currentState, payload) {
    currentState.courseLessons = payload;
  },
  [types.GET_COURSE_DETAIL](currentState, payload) {
    currentState.selectedPlanId = payload.id;
    currentState.details = payload;
    currentState.joinStatus = hasJoinedCourse(payload);
    currentState.sourceType = 'img';
  },
  [types.JOIN_COURSE](currentState) {
    currentState.joinStatus = true;
  },
  [types.SET_SOURCETYPE](currentState, payload) {
    currentState.sourceType = payload.sourceType;
    currentState.taskId = payload.taskId;
  }
};

const actions = {
  getCourseDetail({ commit }, { courseId }) {
    const query = { courseId };
    return Promise.all([
      Api.getCourseDetail({ query }),
      Api.getCourseLessons({ query })
    ]).then(([courseDetail, coursePlan]) => {
      commit(types.GET_COURSE_DETAIL, courseDetail);
      commit(types.GET_COURSE_LESSONS, coursePlan);
      return [courseDetail, coursePlan];
    });
  },
  joinCourse({ commit }, { id }) {
    return Api.joinCourse({
      query: {
        id
      }
    }).then(res => {
      commit(types.JOIN_COURSE, res);
      return res;
    });
  }
};

export default {
  namespaced: true,
  state,
  actions,
  mutations
};
