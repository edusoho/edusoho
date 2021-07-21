import Vuex from 'vuex';
import Vue from 'vue';

import { getLanguage } from '@/lang/index';

import * as getters from './getters';
import * as actions from './actions';
import mutations from './mutations';
import course from './modules/course';
import classroom from './modules/classroom';
import ItemBank from './modules/item-bank-exercise';
import goods from './modules/goods';
import vip from './modules/vip';

Vue.use(Vuex);

const state = {
  isLoading: false,
  token: null,
  user: {},
  smsToken: '',
  settings: {},
  courseSettings: {},
  title: '',
  vipSettings: {},
  wechatSwitch: false,
  vipSwitch: false,
  couponSwitch: null,
  socialBinded: {
    wx: true,
  },
  DrpSwitch: false, // 分销插件
  cloudSdkCdn: '', // 气球云 SDK 的 CDN 地址
  cloudPlayServer: '', // 云资源播放 SDK 的 API 地址
  language: getLanguage(), // 中英文
};

export default new Vuex.Store({
  state,
  getters,
  actions,
  mutations,
  modules: {
    course,
    classroom,
    ItemBank,
    goods,
    vip,
  },
});
