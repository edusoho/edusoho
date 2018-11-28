<template>
  <div class="more" :class="{ 'more__still': selecting  }">
    <treeSelect
      :selectItems="selectItems"
      v-model="selectedData"
      @selectedChange="setQuery"
      @selectToggled="toggleHandler"
    ></treeSelect>
    <lazyLoading
      :courseList="courseList"
      :isAllData="isAllCourse"
      :courseItemType="courseItemType"
      v-model="isRequestCompile"
      @needRequest="sendRequest"
      :typeList="'course_list'"
    ></lazyLoading>
    <emptyCourse v-if="isEmptyCourse && isRequestCompile" :has-button="false" :type="'course_list'"></emptyCourse>
  </div>
</template>

<script>
  import Api from '@/api';
  import treeSelect from '../../components/e-tree-select/e-tree-select.vue';
  import lazyLoading from '../../components/e-lazy-loading/e-lazy-loading.vue';
  import emptyCourse from '../../learning/emptyCourse/emptyCourse.vue';
  import { mapMutations } from 'vuex';
  import * as types from '@/store/mutation-types';

  export default {
    components: {
      treeSelect,
      lazyLoading,
      emptyCourse
    },
    data() {
      return {
        selectItems: [],
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
          sort: 'sort'
        },
      };
    },
    watch: {
      selectedData() {
        this.initCourseList();
        const setting = {
            offset: this.offset,
            limit: this.limit
          };

        this.requestCourses(setting)
          .then(() => {
            if (this.courseList.length !== 0) {
              this.isEmptyCourse = false;
            } else {
              this.isEmptyCourse = true;
            }
          });
      }
    },
    methods: {
      setQuery(value) {
        this.selectedData = value;
      },

      initCourseList() {
        this.isRequestCompile = false;
        this.isAllCourse = false;
        this.courseList = [];
        this.offset = 0;
      },

      judegIsAllCourse(courseInfomation) {
        if (this.courseList.length == courseInfomation.paging.total) {
          return true
        }
        return false
      },

      requestCourses(setting) {
        this.isRequestCompile = false;
        const config = Object.assign(this.selectedData, setting);
        return Api.getCourseList({
          params: config
        }).then((data) => {
          data.data.forEach(element => {
            this.courseList.push(element);
          })
          let isAllCourse= this.judegIsAllCourse(data);
          if (!isAllCourse) {
            this.offset = this.courseList.length;
          }
          this.isAllCourse = isAllCourse;
          this.isRequestCompile = true;
        }).catch((err) => {
          console.log(err, 'error');
        })
      },

      sendRequest() {
        const args = {
          offset: this.offset,
          limit: this.limit
        };

        if (!this.isAllCourse) this.requestCourses(args);
      },

      transform(obj) {
        let config = {};
        const arr = Object.keys(obj);
        if (!arr.length) {
          return {
            categoryId: this.categoryId,
            type: this.type,
            sort: this.sort,
          }
        }
        arr.forEach((current, index) => {
          config[this.queryForm[current]] = obj[current];
        });
        console.log(config, 'arr config');
        return config;
      },
      toggleHandler(value) {
        this.selecting = value;
      },
    },
    created() {
      this.selectedData = this.transform(this.$route.query);
      // 合并参数
      const config = Object.assign(this.selectedData, {
            offset: this.offset,
            limit: this.limit
          });
      const categoryDefaultData = [
        {
          data: [],
          moduleType: 'tree',
          text: '分类',
          type: 'category'
        },
        {
          data: [
            {text: '全部', type: 'all'},
            {text: '课程', type: 'normal'},
            {text: '直播', type: 'live'}
          ],
          moduleType: 'normal',
          text: '课程类型',
          type: 'courseType'
        },
        {
          data: [
            {text: '推荐', type: 'recommendedSeq'},
            {text: '热门', type: '"-studentNum"'},
            {text: '最新', type: '-createdTime'}
          ],
          moduleType: 'normal',
          text: '课程类型',
          type: 'sort'
        }
      ]

      // 老接口数据，会被替换暂不处理
      // Api.getSelectItems()
      //   .then((data) => {
      //     data[0].data.unshift({
      //       name: '全部',
      //       id: '0'
      //     });
      //     data[1].data.unshift({
      //       text: '全部',
      //       type: 'all'
      //     });
      //     const items = Object.values(data)
      //     items.pop();
      //     this.selectItems = items;
      //   });

      // 获取班级分类数据
      Api.getCourseCategories()
        .then((data) => {
          const item = data;
          item.unshift({
            name: '全部',
            id: '0'
          });
          categoryDefaultData[0].data = item;
          this.selectItems = categoryDefaultData;
        })
    }
  }
</script>
