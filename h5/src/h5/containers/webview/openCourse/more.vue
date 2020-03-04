<template>
  <div class="openCourse">
    <treeSelects
      :select-items="selectItems"
      :categories="categories"
      :treeMenuLevel="treeMenuLevel"
      :selectedData="selectedData"
      type="openCourse"
      @selectedChange="setQuery"
      @selectToggled="toggleHandler"
    />
    <van-tabs 
    class="openCourse__tabs"
    v-model="isReplay" 
    color="#03C777"
    title-active-color="#03C777"
    line-width="16px"
    :border="false"
    animated 
    >
      <van-tab title="直播">
        <infinite-scroll
          :openCourseDate="courseDate"
          :openCourseList="courseList"
          :is-all-data="isAllCourse"
          :is-request-compile="isRequestCompile"
          :type-list="'open_course_list'"
          :is-app-use="isAppUse"
          @needRequest="sendRequest"
          @resetData="initCourseList"
        />
      </van-tab>
      <van-tab title="回放">
        <infinite-scroll
          :openCourseDate="courseDate"
          :openCourseList="courseList"
          :is-all-data="isAllCourse"
          :is-request-compile="isRequestCompile"
          :type-list="'open_course_list'"
          :is-app-use="isAppUse"
          @needRequest="sendRequest"
          @resetData="initCourseList"
        />
      </van-tab>
    </van-tabs>
    <empty v-if="isEmptyCourse && isRequestCompile" text="暂无课程" class="empty__couse" />
    <back-top  icon="icon-top" color="#20B573"/>  
  </div>
</template>

<script>
import Api from "@/api";
import infiniteScroll from "&/components/e-infinite-scroll/e-infinite-scroll.vue";
import treeSelects from "&/components/e-tree-selects/e-tree-selects.vue";
import empty from "&/components/e-empty/e-empty.vue";
import backTop from "&/components/e-back-top/e-back-top.vue";
import { mapMutations } from "vuex";
import CATEGORY_DEFAULT from "@/config/category-default-config.js";
import { formatChinaYear } from "@/utils/date-toolkit";
export default {
  name: "more_openCourse",
  components: {
    infiniteScroll,
    treeSelects,
    empty,
    backTop
  },
  data() {
    return {
      isAppUse: true, //是否被app调用
      selectedData: {},
      isRequestCompile: false,
      isAllCourse: false,
      isEmptyCourse: true,
      course:[],
      courseList: {},
      courseDate:[],
      offset: 0,
      limit: 10,
      type: "all",
      categoryId: 0,
      isReplay:0,
      selecting: false,
      queryForm: {
        courseType: "type"
      },
      treeMenuLevel: 1,
      selectItems: CATEGORY_DEFAULT["openCourse_list"],
      categories: []
    };
  },
  watch: {
    isReplay: function (newVal, oldVal) {
      if(newVal===oldVal){
        return;
      }
      this.setQuery();
    }
  },
  created() {
    this.setTitle();
    this.selectedData = this.transform(this.$route.query);
    this.getCourseCategories();
    this.setQuery();
  },
  methods: {
    setTitle() {
      window.postNativeMessage({
        action: "kuozhi_native_header",
        data: { title: "所有公开课" }
      });
    },
    setQuery(value) {
      if (value) {
        this.selectedData = value;
      }
      this.initCourseList();
      this.getCourseList();
    },
    // 获取课程分类数据
    getCourseCategories() {
      Api.getCourseCategories()
        .then(data => {
          this.formateCategories(data);
        })
        .catch(error => {
          this.sendError(error);
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
      this.course = [];
      this.courseList={};
      this.courseDate=[];
      this.offset = 0;
    },
    getCourseList() {
      const setting = {
        offset: this.offset,
        limit: this.limit,
        isReplay:this.isReplay
      };

      this.requestCourses(setting).then(() => {
        this.isEmptyCourse = this.course.length === 0;
      });
    },
    judegIsAllCourse(courseInfomation) {
      return this.course.length == courseInfomation.paging.total;
    },
    requestCourses(setting) {
      this.isRequestCompile = false;
      const config = Object.assign(this.selectedData, setting);
      return Api.getOpenCourseList({
        params: config
      })
        .then(data => {
          this.formateData(data);
          this.isRequestCompile = true;
        })
        .catch(error => {
          this.sendError(error);
        });
    },
    formateData(data) {
      let courseDate = this.courseDate;
      data.data.forEach(item => {
        let date = formatChinaYear(new Date(item.createdTime));
        courseDate.push(date);
        if (!this.courseList[date]) {
          this.$set(this.courseList, date, []);
        }
        this.courseList[date].push(item);
      });
      this.courseDate = Array.from(new Set(courseDate));

      this.course = this.course.concat(data.data);
      this.isAllCourse = this.judegIsAllCourse(data);
      if (!this.isAllCourse) {
        this.offset = this.course.length;
      }
    },
    sendRequest() {
      const args = {
        offset: this.offset,
        limit: this.limit
      };

      if (!this.isAllCourse) this.requestCourses(args);
    },
    transform(obj) {
      const config = {};
      const arr = Object.keys(obj);
      if (!arr.length) {
        return {
          categoryId: this.categoryId,
        };
      }
      arr.forEach((current, index) => {
        config[this.queryForm[current]] = obj[current];
      });
      return config;
    },
    toggleHandler(value) {
      this.selecting = value;
    },
    sendError(error) {
      window.postNativeMessage({
        action: "kuozhi_h5_error",
        data: {
          code: error.code,
          message: error.message
        }
      });
    }
  }
};
</script>

<style>
</style>