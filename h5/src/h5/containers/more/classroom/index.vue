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

    <lazyLoading
      :course-list="courseList"
      :is-all-data="true"
      :normal-tag-show="false"
      :vip-tag-show="true"
      :course-item-type="courseItemType"
      :is-request-compile="isRequestCompile"
      :type-list="'classroom_list'"
      @needRequest="sendRequest"
      :showNumberData="showNumberData"
    />
    <emptyCourse
      v-if="isEmptyCourse && isRequestCompile"
      :has-button="false"
      :type="'classroom_list'"
      :text="$t('more.noClass')"
    />
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';
import lazyLoading from '&/components/e-lazy-loading/e-lazy-loading.vue';
import emptyCourse from '../../learning/emptyCourse/emptyCourse.vue';
import { mapState, mapActions } from 'vuex';
import CATEGORY_DEFAULT from '@/config/category-default-config.js';

export default {
  components: {
    lazyLoading,
    emptyCourse,
  },
  data() {
    return {
      selectedData: {},
      courseItemType: 'price',
      isRequestCompile: false,
      isAllClassroom: false,
      isEmptyCourse: true,
      courseList: [],
      offset: 0,
      limit: 10,
      selecting: false,
      dataDefault: CATEGORY_DEFAULT.new_classroom_list,
      dropdownData: [],
      showNumberData: '',
    };
  },
  computed: {
    ...mapState({
      searchClassRoomList: state => state.classroom.searchClassRoomList,
      vipLevels: state => state.vip.vipLevels,
      vipSwitch: state => state.vipSwitch,
      vipOpenStatus: state => state.vip.vipOpenStatus,
    }),
  },
  watch: {
    selectedData() {
      const { courseList, selectedData, paging } = this.searchClassRoomList;

      if (this.isSelectedDataSame(selectedData)) {
        this.courseList = courseList;
        this.requestClassRoomSuccess(paging);

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
    ...mapActions('classroom', ['setClassRoomList']),
    ...mapActions('vip', ['getVipLevels', 'getVipOpenStatus']),

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
      const res = await Api.getClassCategories();
      this.dataDefault[0].options = this.initOptions({
        text: this.$t('more.all'),
        data: res,
      });
      this.dataDefault[1].options = this.initOptions({
        text: this.$t('more.membersClass'),
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
        name: 'more_class',
        query: value,
      });
    },

    initCourseList() {
      this.isRequestCompile = false;
      this.isAllClassroom = false;
      this.courseList = [];
      this.offset = 0;
    },

    judegIsAllClassroom(paging) {
      return this.courseList.length == paging.total;
    },

    requestCourses(setting) {
      this.isRequestCompile = false;
      const config = Object.assign({}, this.selectedData, setting);
      return Api.getClassList({
        params: config,
      })
        .then(({ data, paging }) => {
          data.forEach(element => {
            this.courseList.push(element);
          });
          this.setClassRoomList({
            selectedData: this.selectedData,
            courseList: this.courseList,
            paging,
          });
          this.requestClassRoomSuccess(paging);
        })
        .catch(err => {
          console.log(err, 'error');
        });
    },

    requestClassRoomSuccess(paging = {}) {
      this.isAllClassroom = this.judegIsAllClassroom(paging);
      if (!this.isAllClassroom) {
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

      if (!this.isAllClassroom) this.requestCourses(args);
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
