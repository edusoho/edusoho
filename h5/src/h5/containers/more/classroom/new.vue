<template>
  <div class="app">
    <e-navbar  title="所有班级"/>

    <treeSelects
      :select-items="selectItems"
      :categories="categories"
      :treeMenuLevel="treeMenuLevel"
      :selectedData="selectedData"
      type="classroom"
      @selectedChange="setQuery"
      @selectToggled="toggleHandler"
    />

    <infinite-scroll
      :course-list="courseList"
      :is-all-data="true"
      :normal-tag-show="false"
      :vip-tag-show="true"
      :course-item-type="courseItemType"
      :is-request-compile="isRequestCompile"
      :type-list="'classroom_list'"
      :is-app-use="isAppUse"
      @needRequest="sendRequest"
    />
    <empty 
     v-if="isEmptyCourse && isRequestCompile" 
     text="暂无班级"
     class="empty__couse"/>
  </div>
</template>

<script>
import Api from "@/api";
import infiniteScroll from "&/components/e-infinite-scroll/e-infinite-scroll.vue";
import treeSelects from "&/components/e-tree-selects/e-tree-selects.vue";
import ENavbar from "&/components/e-navbar/e-navbar.vue";
import empty from '&/components/e-empty/e-empty.vue'
import { mapMutations } from "vuex";
import * as types from "@/store/mutation-types";
import CATEGORY_DEFAULT from "@/config/category-default-config.js";
export default {
  name: "more_class_new",
  components: {
    infiniteScroll,
    treeSelects,
    empty,
    ENavbar
  },
  data() {
    return {
      isAppUse:true, //是否被app调用
      selectedData: {},
      courseItemType: "price",
      isRequestCompile: false,
      isAllCourse: false,
      isEmptyCourse: true,
      courseList: [],
      offset: 0,
      limit: 10,
      type: "all",
      categoryId: 0,
      sort: "recommendedSeq",
      selecting: false,
      queryForm: {
        courseType: "type",
        category: "categoryId",
        sort: "sort"
      },
      treeMenuLevel: 1,
      selectItems: CATEGORY_DEFAULT["classroom_list"],
      categories: []
    };
  },
  created() {
    this.selectedData = this.transform(this.$route.query);
    this.getClassCategories();
    this.setQuery();
  },
  methods: {
    setQuery(value) {
      if(value){
          this.selectedData=value;
      }
       this.initCourseList();
       this.getClassList();
    },
    // 获取班级分类数据
    getClassCategories() {
      Api.getClassCategories().then(data => {
        this.formateCategories(data);
      });
    },
    formateCategories(categories) {
      categories.unshift({
        name: "全部",
        id: "0",
        children: []
      });
      categories.forEach(item => {
        if (item.children.length) {
          this.treeMenuLevel = 2;
        }
      });
      this.categories = categories;
    },
    initCourseList() {
      this.isRequestCompile = false;
      this.isAllCourse = false;
      this.courseList = [];
      this.offset = 0;
    },
    getClassList() {
      const setting = {
        offset: this.offset,
        limit: this.limit
      };

      this.requestClass(setting).then(() => {
        this.isEmptyCourse = this.courseList.length === 0;
      });
    },
    judegIsAllCourse(courseInfomation) {
      return this.courseList.length == courseInfomation.paging.total;
    },
    requestClass(setting) {
      this.isRequestCompile = false;
      const config = Object.assign(this.selectedData, setting);

      return Api.getClassList({
        params: config
      })
        .then(data => {
          this.formateData(data);
          this.isRequestCompile = true;
        })
        .catch(err => {
          console.log(err, "error");
        });
    },
    formateData(data) {
      this.courseList = this.courseList.concat(data.data);
      this.isAllCourse = this.judegIsAllCourse(data);
      if (!this.isAllCourse) {
        this.offset = this.courseList.length;
      }
    },
    sendRequest() {
      const args = {
        offset: this.offset,
        limit: this.limit
      };

      if (!this.isAllCourse) this.requestClass(args);
    },
    transform(obj) {
      const config = {};
      const arr = Object.keys(obj);
      if (!arr.length) {
        return {
          categoryId: this.categoryId,
          type: this.type,
          sort: this.sort
        };
      }
      arr.forEach((current, index) => {
        config[this.queryForm[current]] = obj[current];
      });
      return config;
    },
    toggleHandler(value) {
      this.selecting = value;
    }
  }
};
</script>

<style>
</style>