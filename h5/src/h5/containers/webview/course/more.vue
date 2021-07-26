<template>
  <div :class="{ more__still: selecting }" class="more">
    <van-dropdown-menu active-color="#1989fa">
      <template v-for="(item, index) in dropdownData" @change="change">
        <van-dropdown-item
          v-if="item.type === 'vipLevelId' ? vipSwitch : true"
          :key="index"
          v-model="item.value"
          :options="item.options"
          @change="change"
        />
      </template>
    </van-dropdown-menu>

    <infinite-scroll
      :course-list="courseList"
      :is-all-data="isAllCourse"
      :course-item-type="courseItemType"
      :is-request-compile="isRequestCompile"
      :vip-tag-show="true"
      :type-list="'course_list'"
      :is-app-use="isAppUse"
      @needRequest="sendRequest"
      :showNumberData="showNumberData"
    />
    <empty
      v-if="isEmptyCourse && isRequestCompile"
      text="暂无课程"
      class="empty__couse"
    />

    <back-top icon="icon-top" color="#20B573" />
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';
import infiniteScroll from '&/components/e-infinite-scroll/e-infinite-scroll.vue';
import empty from '&/components/e-empty/e-empty.vue';
import backTop from '&/components/e-back-top/e-back-top.vue';
import { mapState, mapActions } from 'vuex';
import CATEGORY_DEFAULT from '@/config/category-default-config.js';

export default {
  components: {
    infiniteScroll,
    empty,
    backTop,
  },
  data() {
    return {
      isAppUse: true, // 是否被app调用
      selectedData: {},
      courseItemType: 'price',
      isRequestCompile: false,
      isAllCourse: false,
      isEmptyCourse: true,
      courseList: [],
      offset: 0,
      limit: 10,
      selecting: false,
      showNumberData: '',
      dataDefault: CATEGORY_DEFAULT.new_course_list,
      dropdownData: [],
    };
  },
  computed: {
    ...mapState({
      searchCourseList: state => state.course.searchCourseList,
      vipLevels: state => state.vip.vipLevels,
      vipSwitch: state => state.vipSwitch,
      vipOpenStatus: state => state.vip.vipOpenStatus,
    }),
  },
  watch: {
    selectedData() {
      const { courseList, selectedData, paging } = this.searchCourseList;

      if (this.isSelectedDataSame(selectedData)) {
        this.courseList = courseList;
        this.requestCoursesSuccess(paging);

        return;
      }

      this.initCourseList();
      const setting = {
        offset: this.offset,
        limit: this.limit,
      };
      this.requestCourses(setting);
    },
  },
  async created() {
    window.scroll(0, 0);
    this.setTitle();

    if (this.vipOpenStatus === null) {
      await this.getVipOpenStatus();
    }

    // vuex 中会员等级列表为空
    if (this.vipOpenStatus && !this.vipLevels.length) {
      await this.getVipLevels();
    }

    this.initI18n();

    // 初始化下拉筛选数据
    this.initDropdownData();

    this.getGoodSettings();
  },
  methods: {
    ...mapActions('course', ['setCourseList']),
    ...mapActions('vip', ['getVipLevels', 'getVipOpenStatus']),

    setTitle() {
      window.postNativeMessage({
        action: 'kuozhi_native_header',
        data: { title: '所有课程' },
      });
    },

    initI18n() {
      _.forEach(this.dataDefault, item => {
        _.forEach(item.options, option => {
          const { text, i18n } = option;
          option.text = i18n ? this.$t(text) : text;
        });
      });
    },

    async initDropdownData() {
      // 获取班级分类数据
      const res = await Api.getCourseCategories();
      this.dataDefault[0].options = this.initOptions({
        text: this.$t('more.all'),
        data: res,
      });
      this.dataDefault[2].options = this.initOptions({
        text: this.$t('more.membersCourse'),
        data: this.vipLevels,
      });

      const query = this.$route.query;
      this.dataDefault.forEach((item, index) => {
        const value = query[item.type];
        if (value) {
          this.dataDefault[index].value = value;
        }
      });

      this.dropdownData = this.dataDefault;
      this.selectedData = this.transform(this.$route.query);
    },

    initOptions({ text, data }) {
      const options = [{ text: text, value: '0' }];

      data.forEach(item => {
        options.push({
          text: item.name,
          value: item.id,
        });
      });
      return options;
    },

    change() {
      this.selectedData = this.getSelectedData();
      this.setQuery(this.selectedData);
    },

    transform(obj = {}) {
      return Object.assign(this.getSelectedData(), obj);
    },

    getSelectedData() {
      const selectedData = {};
      this.dropdownData.forEach(item => {
        const { type, value } = item;
        if (type === 'vipLevelId' && (!this.vipSwitch || value == '0')) {
          return;
        }
        selectedData[type] = value;
      });
      return selectedData;
    },

    setQuery(value) {
      this.$router.replace({
        name: 'more_course_new',
        query: value,
      });
    },

    initCourseList() {
      this.isRequestCompile = false;
      this.isAllCourse = false;
      this.courseList = [];
      this.offset = 0;
    },

    judegIsAllCourse(paging) {
      return this.courseList.length == paging.total;
    },

    requestCourses(setting) {
      this.isRequestCompile = false;
      const config = Object.assign({}, this.selectedData, setting);
      return Api.getCourseList({
        params: config,
      })
        .then(({ data, paging }) => {
          data.forEach(element => {
            this.courseList.push(element);
          });
          this.setCourseList({
            selectedData: this.selectedData,
            courseList: this.courseList,
            paging,
          });
          this.requestCoursesSuccess(paging);
        })
        .catch(err => {
          console.log(err, 'error');
        });
    },

    requestCoursesSuccess(paging = {}) {
      this.isAllCourse = this.judegIsAllCourse(paging);
      if (!this.isAllCourse) {
        this.offset = this.courseList.length;
      }
      this.isRequestCompile = true;
      this.isEmptyCourse = this.courseList.length === 0;
    },

    sendRequest() {
      const args = {
        offset: this.offset,
        limit: this.limit,
      };

      if (!this.isAllCourse) this.requestCourses(args);
    },

    toggleHandler(value) {
      this.selecting = value;
    },
    isSelectedDataSame(selectedData) {
      const oldLength = Object.keys(selectedData).length;
      const newLength = Object.keys(this.selectedData).length;

      if (oldLength != newLength) return false;

      for (const key in this.selectedData) {
        if (this.selectedData[key] != selectedData[key]) {
          return false;
        }
      }

      return true;
    },
    getGoodSettings() {
      Api.getSettings({
        query: {
          type: 'goods',
        },
      }).then(res => {
        this.showNumberData = res.show_number_data;
      });
    },
  },
};
</script>
