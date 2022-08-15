<template>
  <div :class="{ more__still: selecting }" class="more">
    <div style="display: flex;background-color: #fff;box-shadow: 0 2px 12px rgb(100 101 102 / 12%);">
      <div
        v-if="dropdownData && dropdownData.length > 0" 
        class="itembank-category text-overflow" 
        @click="showItembankCategoryPopup = true"
      >
        <span class="itembank-category__title">{{ currentItembankCategoryText }}</span>
      </div>
      <div style="flex: 1;">
        <van-dropdown-menu active-color="#1989fa">
          <van-dropdown-item
            v-for="(item, index) in dropdownData"
            :key="index"
            v-model="item.value"
            :options="item.options"
            @change="setQuery"
          />
        </van-dropdown-menu>
      </div>
    </div>

    <van-popup v-model="showItembankCategoryPopup" position="bottom">
      <van-cascader
        v-model="categoryId"
        :options="itembankCategories"
        @close="showItembankCategoryPopup = false"
        @finish="onFinish"
      />
    </van-popup>
    
    <lazyLoading
      :course-list="courseList"
      :is-all-data="isAllCourse"
      :course-item-type="courseItemType"
      :is-request-compile="isRequestCompile"
      :vip-tag-show="true"
      :type-list="'item_bank_exercise'"
      @needRequest="sendRequest"
    />
    <emptyCourse
      v-if="isEmptyCourse && isRequestCompile"
      :has-button="false"
      :type="'course_list'"
      text="暂无课程"
    />
  </div>
</template>

<script>
import Api from '@/api';
import lazyLoading from '&/components/e-lazy-loading/e-lazy-loading.vue';
import emptyCourse from '../../learning/emptyCourse/emptyCourse.vue';
import { mapState, mapActions } from 'vuex';

export default {
  components: {
    lazyLoading,
    emptyCourse,
  },
  data() {
    return {
      dropdownData: [],
      selectedData: {},
      courseItemType: 'price',
      isRequestCompile: false,
      isAllCourse: false,
      isEmptyCourse: true,
      courseList: [],
      offset: 0,
      limit: 10,
      type: 'all',
      categoryId: 0,
      sort: 'recommendedSeq',
      selecting: false,
      queryForm: {
        courseType: 'type',
        category: 'categoryId',
        sort: 'sort',
      },
      dataDefault: [
        {
          type: 'sort',
          value: '',
          options: [
            { text: '排序', value: '' },
            { text: '推荐', value: 'recommendedSeq' },
            { text: '热门', value: '-studentNum' },
            { text: '最新', value: '-createdTime' },
          ],
        },
      ],
      itembankCategories: [],
      currentItembankCategoryText: this.$t('more.Classification'),
      showItembankCategoryPopup: false
    };
  },
  computed: {
    ...mapState('ItemBank', {
      searchItemBankList: state => state.searchItemBankList,
    }),
  },
  watch: {
    selectedData() {
      const { courseList, selectedData, paging } = this.searchItemBankList;

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

  created() {
    window.scroll(0, 0);
    this.dropdownData = this.dataDefault;
    this.selectedData = this.transform(this.$route.query);

    this.initItembankCategories();
  },

  methods: {
    ...mapActions('ItemBank', ['setItemBankList']),

    setQuery() {
      this.selectedData = this.getSelectedData();
      this.selectedData.categoryId = this.categoryId;

      this.$router.replace({
        name: 'more_itembank',
        query: this.selectedData,
      });
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

    async initItembankCategories() {
      const data = await Api.getItemBankCategoriesNew()

      this.itembankCategories = this.initOptions({
        text: this.$t('more.all'),
        value: '0',
        data
      });

      const categoryId = this.$route.query.categoryId
      if (categoryId && categoryId !== '0') {
        this.currentItembankCategoryText = this.getCategoryDescById(this.itembankCategories, categoryId);
      }
    },

    onFinish({ selectedOptions }) {
      this.showItembankCategoryPopup = false;
      this.currentItembankCategoryText = this.getCategoryDescById(this.itembankCategories, selectedOptions[selectedOptions.length - 1].value);
      this.setQuery()
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
      return Api.getItemBankList({
        params: config,
      })
        .then(({ data, paging }) => {
          data.forEach(element => {
            this.courseList.push(element);
          });
          this.setItemBankList({
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

    transform(obj = {}) {
      return Object.assign(
        {
          categoryId: this.categoryId,
          type: this.type,
          sort: this.sort,
        },
        obj,
      );
    },
    toggleHandler(value) {
      this.selecting = value;
    },
    isSelectedDataSame(selectedData) {
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
  
  .itembank-category {
    display: flex;
    flex: 1;
    justify-content: center;
    align-items: center;
  }

  .itembank-category__title {
    position: relative;
    box-sizing: border-box;
    max-width: 100%;
    padding: 0 8px;
    color: #323233;
    font-size: 15px;
    line-height: 22px;
  }

  .itembank-category__title::after {
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
