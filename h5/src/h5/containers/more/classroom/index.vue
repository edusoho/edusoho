<template>
  <div :class="{ more__still: selecting }" class="more">
  <div style="display: flex;background-color: #fff;box-shadow: 0 2px 12px rgb(100 101 102 / 12%);">
      <div
        v-if="dropdownData && dropdownData.length > 0"
        class="class-category text-overflow"
        @click="showClassCategoryPopup = true"
      >
        <span class="class-category__title">{{ currentClassCategoryText }}</span>
      </div>
      <div style="flex: 2;">
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
      </div>
    </div>

    <van-popup v-model="showClassCategoryPopup" position="bottom">
      <van-cascader
        v-model="currentClassCategoryId"
        :options="classCategories"
        @close="showClassCategoryPopup = false"
        @finish="onFinish"
      />
    </van-popup>

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
      classCategories: [],
      showClassCategoryPopup: false,
      currentClassCategoryText: this.$t('more.Classification'),
      currentClassCategoryId: 0,
    };
  },
  computed: {
    ...mapState({
      searchClassRoomList: state => state.classroom.searchClassRoomList,
      vipLevels: state => state.vip.vipLevels,
      vipSwitch: state => state.vipSwitch,
      vipOpenStatus: state => state.vip.vipOpenStatus,
      showNumberData: state => state.goodsSettings.show_number_data
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

    // 获取班级分类数据
    this.initClassCategories();

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

    async initClassCategories() {
      const res = await Api.getClassCategories();

      this.classCategories = this.initOptions({
        text: this.$t('more.all'),
        data: res,
      });

      const categoryId = this.$route.query.categoryId
      if (categoryId && categoryId !== '0') {
        this.currentClassCategoryText = this.getCategoryDescById(this.classCategories, categoryId)
      }
    },

    async initDropdownData() {
      this.dataDefault[0].options = this.initOptions({
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

    initOptions({ text, value = '0', data }) {
      const options = text ? [{ text, value }] : []

      data.forEach(item => {
        const optionItem = {
          text: item.name,
          value: item.id,
        }

        if (item.children && item.children.length > 0) {
          optionItem.children = this.initOptions({
            text: this.$t('more.all'),
            value: item.id,
            data: item.children
          })
        }

        options.push(optionItem);
      });

      return options;
    },

    change() {
      this.selectedData = this.getSelectedData();
      this.selectedData.categoryId = this.currentClassCategoryId
      this.setQuery(this.selectedData);
    },

    onFinish({ selectedOptions }) {
      this.showClassCategoryPopup = false;
      this.currentClassCategoryText = this.getCategoryDescById(this.classCategories, selectedOptions[selectedOptions.length - 1].value)
      this.change()
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
    getCategoryDescById(categories, categoryId) {
      if (!categories || categories.length === 0) return null

      for (let i = 0; i < categories.length; i++) {
        const currentCategory = categories[i]

        if (currentCategory.value === categoryId) {
          return currentCategory.text
        }

        const categoryText = this.getCategoryDescById(currentCategory.children, categoryId)

        if (categoryText) return categoryText
      }

      return null
    }
  },
};
</script>

<style scoped>

  .more {
    background-color: #f7f9fa;
  }

  .class-category {
    display: flex;
    flex: 1;
    justify-content: center;
    align-items: center;
  }

  .class-category__title {
    position: relative;
    max-width: 100%;
    padding: 0 8px;
    color: #323233;
    font-size: 15px;
    line-height: 22px;
  }

  .class-category__title::after {
    position: absolute;
    top: 50%;
    right: -4px;
    margin-top: -5px;
    border: 3px solid;
    border-color: transparent transparent #dcdee0 #dcdee0;
    -webkit-transform: rotate(-45deg);
    transform: rotate(-45deg);
    opacity: .8;
    content: '';
  }

  .more >>> .van-dropdown-menu__bar {
    box-shadow: none !important;
  }
</style>
