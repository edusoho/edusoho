import Api from '@/api';
import * as types from '../mutation-types';

const state = {
  selectedPlanId: 0,
  joinStatus: false, // 当前计划是否已加入学习
  sourceType: 'img', //
  details: {},
  taskId: 0, // 任务id
  courseLessons: [], // 课程中所有任务
  nextStudy: {}, // 下一次学习
  OptimizationCourseLessons: [] // 优化后的课程中所有任务
};

const hasJoinedCourse = course => course.member;

const mutations = {
  [types.GET_COURSE_LESSONS](currentState, payload) {
    currentState.courseLessons = payload;
  },
  [types.GET_OPTIMIZATION_COURSE_LESSONS](currentState, payload) {
    currentState.OptimizationCourseLessons = payload;
  },
  [types.GET_NEXT_STUDY](currentState, payload) {
    currentState.nextStudy = payload;
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
  async getCourseLessons({ dispatch }, { courseId }) {
    let s;
    try {
      await dispatch('getNextStudy', { courseId });
      s = await dispatch('getCourse', { courseId });
    } catch (e) {
      s = await dispatch('getCourse', { courseId });
    }
    return s[2];
  },
  getCourse({ commit }, { courseId }) {
    const query = { courseId };
    commit('UPDATE_LOADING_STATUS', true, { root: true }); // -> 'someMutation'
    return Promise.all([
      Api.getCourseLessons({ query }),
      Api.getOptimizationCourseLessons({ query }),
      Api.getCourseDetail({ query })
    ]).then(([coursePlan, OptimizationCoursePlan, courseDetail]) => {
      commit(types.GET_COURSE_LESSONS, coursePlan);
      commit(types.GET_OPTIMIZATION_COURSE_LESSONS, OptimizationCoursePlan);
      commit(types.GET_COURSE_DETAIL, courseDetail);
      commit('UPDATE_LOADING_STATUS', false, { root: true }); // -> 'someMutation'
      return [coursePlan, OptimizationCoursePlan, courseDetail];
    });
  },
  getBeforeCourse({ commit }, { courseId }) {
    const query = { courseId };
    return Promise.all([
      Api.getCourseLessons({ query })
    ]).then(([coursePlan]) => {
      commit(types.GET_COURSE_LESSONS, coursePlan);
      return [coursePlan];
    });
  },
  getAfterCourse({ commit }, { courseId }) {
    const query = { courseId };
    return Promise.all([
      Api.getOptimizationCourseLessons({ query })
    ]).then(([OptimizationCoursePlan]) => {
      commit(types.GET_OPTIMIZATION_COURSE_LESSONS, OptimizationCoursePlan);
      // dispatch('getNextStudy', { courseId });
      return [OptimizationCoursePlan];
    });
  },
  getCourseDetail({ commit }, { courseId }) {
    const query = { courseId };
    return Promise.all([
      Api.getCourseDetail({ query })
    ]).then(([courseDetail]) => {
      commit(types.GET_COURSE_DETAIL, courseDetail);
      return courseDetail;
    });
  },
  getNextStudy({ commit }, { courseId }) {
    const query = { courseId };
    return Promise.all([
      Api.getNextStudy({ query })
    ]).then(([nextStudy]) => {
      commit(types.GET_NEXT_STUDY, nextStudy);
      return [nextStudy];
    });
  },
  joinCourse({ commit }, { id }) {
    return Api.joinCourse({
      query: {
        id
      }
    }).then(res => {
      // 返回空对象，表示加入失败，需要去创建订单购买
      if (!(Object.keys(res).length === 0)) {
        commit(types.JOIN_COURSE, res);
      }
      return res;
    });
  },
  // eslint-disable-next-line no-unused-vars
  handExamdo({ commit }, datas) {
    // eslint-disable-next-line prefer-const
    let { answer, resultId, beginTime, endTime, userId } = { ...datas };
    beginTime *= 1000;
    let usedTime = Math.ceil((endTime - beginTime) / 1000);

    // 如果是不限时间限制，使用时间在本地有记录，如果有时间限制，使用时间在本地无记录
    const localuseTime = `${userId}-${resultId}-usedTime`;
    if (localStorage.getItem(localuseTime)) {
      usedTime = localStorage.getItem(localuseTime);
    }

    return new Promise((resolve, reject) => {
      Api.handExam({
        data: {
          data: answer,
          resultId,
          usedTime
        }
      })
        .then(res => {
          localStorage.removeItem(`${userId}-${resultId}`);
          localStorage.removeItem(`${userId}-${resultId}-time`);
          resolve(res);
        })
        .catch(err => {
          reject(err);
        });
    });
  },
  // eslint-disable-next-line no-unused-vars
  handHomeworkdo({ commit }, datas) {
    // eslint-disable-next-line prefer-const
    let { answer, homeworkResultId, homeworkId, userId } = { ...datas };

    // 如果是不限时间限制，使用时间在本地有记录，如果有时间限制，使用时间在本地无记录
    const localuseTime = `${userId}-${homeworkResultId}-usedTime`;
    const usedTime = Number(localStorage.getItem(localuseTime)) || 0;
    console.log(answer);
    console.log(homeworkResultId);
    console.log(homeworkId);
    console.log(usedTime);

    return new Promise((resolve, reject) => {
      Api.handHomework({
        query: {
          homeworkId,
          homeworkResultId
        },
        data: {
          data: answer,
          usedTime
        }
      })
        .then(res => {
          console.log(res);
          localStorage.removeItem(`${userId}-${homeworkResultId}`);
          localStorage.removeItem(localuseTime);
          resolve(res);
        })
        .catch(err => {
          reject(err);
        });
    });
  }
};

export default {
  namespaced: true,
  state,
  actions,
  mutations
};
