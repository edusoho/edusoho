import Vue from 'vue';
import _ from 'lodash';

export const state = Vue.observable({
  vipSetting: {},
  couponSetting: {},
  courseCategory: [], // 课程分类数据
  classroomCategory: [], // 班级分类数据
  openCourseCategory: [], // 公开课分类数据
  courseCategories: [], // 课程分类
  classroomCategories: [], // 班级分类
  itemBankCategories: [] // 题库分类
});

function deleteEmptyChildren(data) {
  _.forEach(data, item => {
    if (!_.size(item.children)) {
      delete item.children;
    } else {
      deleteEmptyChildren(item.children);
    }
  });
}

export const mutations = {
  setVipSetting(data) {
    state.vipSetting = data;
  },

  setCouponSetting(data) {
    state.couponSetting = data;
  },

  setCourseCategory(data) {
    state.courseCategory = data;
  },

  setClassroomCategory(data) {
    state.classroomCategory = data;
  },

  setOpenCourseCategory(data) {
    state.openCourseCategory = data;
  },

  setCourseCategories(data) {
    deleteEmptyChildren(data);
    data.unshift({ name: '全部', id: '0' });
    state.courseCategories = data;
  },

  setClassroomCategories(data) {
    deleteEmptyChildren(data);
    data.unshift({ name: '全部', id: '0' });
    state.classroomCategories = data;
  },

  setItemBankCategories(data) {
    deleteEmptyChildren(data);
    data.unshift({ name: '全部', id: '0' });
    state.itemBankCategories = data;
  }
};