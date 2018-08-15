<template>
  <div class="more">
    <treeSelect
      :selectItems="selectItems"
      v-model="selectedData"
      @selectedChange="setQuery"
    ></treeSelect>
    <lazyLoading
      :courseList="courseList"
      :isAllCourse="isAllCourse"
      :courseItemType="courseItemType"
      v-model="isRequestCompile"
      @needRequest="sendRequest"
      :isMorePage=true
    ></lazyLoading>
    <emptyCourse v-if="isEmptyCourse && isRequestCompile" :has-button="false"></emptyCourse>
  </div>
</template>

<script>
  import Api from '@/api';
  import treeSelect from '../components/e-tree-select/e-tree-select.vue';
  import lazyLoading from '../components/e-lazy-loading/e-lazy-loading.vue';
  import emptyCourse from '../learning/emptyCourse/emptyCourse.vue';
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
      ...mapMutations({
        setNavbarTitle: types.SET_NAVBAR_TITLE
      }),
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
      }
    },
    created() {
      this.selectedData = this.transform(this.$route.query);
      // 合并参数
      const config = Object.assign(this.selectedData, {
            offset: this.offset,
            limit: this.limit
          });
      // 获取select items
      Api.getSelectItems()
        .then((data) => {
          data[0].data.unshift({
            name: '全部',
            id: '0'
          });
          data[1].data.unshift({
            text: '全部',
            type: 'all'
          });
          const items = Object.values(data)
          items.pop();
          this.selectItems = items;
          this.setNavbarTitle(data.title)
        });
      // 根据筛选条件获取相应课程
      // this.requestCourses(config)
      //   .then(() => {
      //     if (this.courseList.length !== 0) {
      //       this.isEmptyCourse = false;
      //     } else {
      //       this.isEmptyCourse = true;
      //     }
      //   });
    }
  }
</script>
