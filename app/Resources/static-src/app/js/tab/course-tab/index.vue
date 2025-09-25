<template>
  <div>
    <div class="panel-heading" style="padding: 10px 0; line-height: 30px;">
      <label class="text-18">{{ 'tab.course_tab.index.my_courses'|trans }}</label>
      <div class="pull-right">
        <form class="search-form" @submit.prevent="getTabData(tabValue)" style="margin-right: 54px;">
          <input class="search-input-content inline-block" v-model="searchValue" type="text" name="title" :placeholder="'tab.course_tab.index.enter_course_name'|trans" />
          <a class="btn inline-block searchCourseBtn es-icon es-icon-search" type="submit" @click="getTabData(tabValue)" style="padding-top: 6px !important;"></a>
        </form>
        <a href="/my/courses/live/calendar" v-show="tabValue == 'learning'" class="live-course-btn">{{ 'tab.course_tab.live_class_schedule'|trans }}</a>
      </div>
    </div>

    <div class="panel-body" style="padding: 0 0 16px 0;">
    <a-tabs
    v-model:activeKey="tabValue"
    :tabBarGutter="0"
    size="small"
    @change="tabOnChange">
      <a-tab-pane key="learning" :tab="'tab.course_tab.learning'|trans">
        <CourseList :courseLists="courseLists"></CourseList>
      </a-tab-pane>
      <a-tab-pane key="learned" :tab="'tab.course_tab.have_completed'|trans">
        <CourseList :courseLists="courseLists"></CourseList>
      </a-tab-pane>
      <a-tab-pane key="expired" :tab="'tab.course_tab.have_expired'|trans">
        <CourseList :courseLists="courseLists" :tabValue="tabValue"></CourseList>
      </a-tab-pane>
      <a-tab-pane key="favorite" :tab="'tab.course_tab.collect'|trans">
        <CourseSetList :courseSets="courseLists"></CourseSetList>
      </a-tab-pane>
    </a-tabs>
    <a-pagination v-if="total>pageSize" :defaultPageSize="pageSize" v-model="current" @change="onChange" :total="total" />
  </div>
  </div>
</template>
<script>
import CourseList from './CourseList.vue';
import CourseSetList from './CourseSetList.vue';
import { Me } from 'common/vue/service/index.js';

export default {
  data(){
    return {
      tabValue: 'learning',
      searchValue: '',
      current: 1,
      courseLists: [],
      total: 0,
      pageSize: 12
    }
  },
  components: {
    CourseList,
    CourseSetList
  },
  async mounted(){
    const params = this.getParams(window.location.href)

    if (params.search) {
      this.searchValue = decodeURIComponent(params.search)
    }

    if (params.type && params.page) {
      this.tabValue = params.type
      this.current = parseInt(params.page)
      await this.getTabData(params.type, params.page);
    } else {
      await this.getTabData(this.tabValue);
    }
  },
  methods: {
    async tabOnChange(key) {
      this.current = 1
      if(key == 'favorite') {
        let params = {
        limit: this.pageSize,
        offset: 0
      }

        const { data, paging } = await Me.searchFavoriteCourses(params)
        this.courseLists = data
        this.total = paging.total
        return
      }

      await this.getTabData(key);
    },
    onChange(pageNumber) {
      window.location.href = window.location.pathname + `?type=${this.tabValue}&page=${pageNumber}${this.searchValue ? `&search=${this.searchValue}` : ''}`
    },
    async getTabData(type, pageNumber=1) {
      let params = {
        title: this.searchValue,
        limit: this.pageSize,
        offset: (pageNumber-1)*this.pageSize,
        type
      }

      if(type == 'favorite') {
        const { data, paging } = await Me.searchFavoriteCourses(params)
        this.courseLists = data
        this.total = paging.total
        return
      }

      const { data, paging } = await Me.searchCourses(params)
      this.courseLists = data
      this.total = paging.total
    },
    getParams(str) {
      let search = str.includes('?') ? str.split('?')[1] : str;
      let params = {};

      search.split('&').forEach(vars => {
        let value = vars.split('=') || [vars];
        params[value[0]] = value[1]
      });

      return params;
    }
  },
}
</script>
