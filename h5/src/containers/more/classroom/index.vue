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
      :isAllData="true"
      :courseItemType="courseItemType"
      :isRequestCompile="isRequestCompile"
      @needRequest="sendRequest"
      :typeList="'classroom_list'"
    ></lazyLoading>
    <emptyCourse v-if="isEmptyCourse && isRequestCompile" :has-button="false" :type="'classroom_list'"></emptyCourse>
  </div>
</template>

<script>
  import Api from '@/api';
  import treeSelect from '../../components/e-tree-select/e-tree-select.vue';
  import lazyLoading from '../../components/e-lazy-loading/e-lazy-loading.vue';
  import emptyCourse from '../../learning/emptyCourse/emptyCourse.vue';
  import { mapMutations } from 'vuex';
  import * as types from '@/store/mutation-types';
  import CATEGORY_DEFAULT from '@/config/category-default-config.js';

  export default {
    components: {
      treeSelect,
      lazyLoading,
      emptyCourse
    },
    data() {
      return {
        selectItems: [],
        copySelectItems: [],
        selectedData: {},
        courseItemType: 'price',
        isRequestCompile: false,
        isAllClassroom: false,
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
        dataDefault: CATEGORY_DEFAULT['classroom_list']
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
        this.isAllClassroom = false;
        this.courseList = [];
        this.offset = 0;
      },

      judegIsAllClassroom(courseInfomation) {
        if (this.courseList.length == courseInfomation.paging.total) {
          return true
        }
        return false
      },

      requestCourses(setting) {
        this.isRequestCompile = false;
        const config = Object.assign(this.selectedData, setting);
        return Api.getClassList({
          params: config
        }).then((data) => {
          data.data.forEach(element => {
            this.courseList.push(element);
          })
          let isAllClassroom = this.judegIsAllClassroom(data);
          if (!isAllClassroom) {
            this.offset = this.courseList.length;
          }
          console.log(data,111);
          this.isAllClassroom = isAllClassroom;
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

        if (!this.isAllClassroom) this.requestCourses(args);
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

      // // 老接口数据，会被替换暂不处理
      // Api.getSelectItems()
      //   .then((data) => {
      //     console.log(data,categoryDefaultData,77777)
      //     data[0].data.unshift({
      //       name: '全部',
      //       id: '0'
      //     });
      //     data[1].data='';
      //     const items = Object.values(data)
      //     items.pop();
      //     this.selectItems = items;
      //   });

      // 获取班级分类数据
      Api.getClassCategories()
        .then((data) => {
          data.unshift({
            name: '全部',
            id: '0'
          });
          this.dataDefault[0].data = data;
          this.selectItems = this.dataDefault;
        })
    },
  }
</script>
