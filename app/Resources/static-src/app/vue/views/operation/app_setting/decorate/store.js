import Vue from 'vue';

export const state = Vue.observable({
  courseCategory: [], // 课程分类数据
  classroomCategory: [], // 班级分类数据
  openCourseCategory: [] // 公开课分类数据
});

export const mutations = {
  setCourseCategory(data) {
    state.courseCategory = data;
  },

  setClassroomCategory(data) {
    state.classroomCategory = data;
  },

  setOpenCourseCategory(data) {
    state.openCourseCategory = data;
  }
};