<template>
  <div>
    <div class="panel-heading" style="border-bottom: 1px solid #f5f5f5; padding: 10px 0; line-height: 30px;">
      <label class="text-18">我的班级</label>
      <div class="pull-right">
        <form class="search-form" @submit.prevent="getTabData(tabValue)" style="margin-right: 54px;">
          <input class="search-input-content inline-block" v-model:value="searchValue" type="text" name="title" placeholder="请输入班级名称" />
          <a class="btn inline-block searchCourseBtn es-icon es-icon-search" type="submit" @click="getTabData(tabValue)"></a>
        </form>
      </div>
    </div>

    <div class="panel-body" style="padding: 0 0 16px 0;">
    <a-tabs 
    v-model:activeKey="tabValue"
    :tabBarGutter="0"
    size="small" 
    @change="tabOnChange">
      <a-tab-pane key="learning" tab="学习中">
        <ClassroomList v-for="(item, index) in courseLists" :key="index" :course="item"></ClassroomList>
        <div v-if="courseLists.length == 0" class="searchEmptyCourse">
          <img class="searchEmptyCourseImg" src="/static-dist/app/img/vue/goods/empty-course.png" alt="">
          <p class="searchEmptyCourseContent">暂无班级</p>
        </div>
      </a-tab-pane>
      <a-tab-pane key="learned" tab="已学完" force-render>
        <ClassroomList v-for="(item, index) in courseLists" :key="index" :course="item"></ClassroomList>
        <div v-if="courseLists.length == 0" class="searchEmptyCourse">
          <img class="searchEmptyCourseImg" src="/static-dist/app/img/vue/goods/empty-course.png" alt="">
          <p class="searchEmptyCourseContent">暂无班级</p>
        </div>
      </a-tab-pane>
      <a-tab-pane key="expired" tab="已过期">
        <ClassroomList v-for="(item, index) in courseLists" :key="index" :course="item"></ClassroomList>
        <div v-if="courseLists.length == 0" class="searchEmptyCourse">
          <img class="searchEmptyCourseImg" src="/static-dist/app/img/vue/goods/empty-course.png" alt="">
          <p class="searchEmptyCourseContent">暂无班级</p>
        </div>
      </a-tab-pane>
    </a-tabs>
    <a-pagination v-if="total>pageSize" :defaultPageSize="pageSize" v-model="current" @change="onChange" :total="total" />
  </div>
  </div>
</template>
<script>
import ClassroomList from './ClassroomList.vue';
export default {
  data(){
    return {
      tabValue: 'learning',
      searchValue: '',
      current: 1,
      courseLists: [],
      total: 130,
      pageSize: 12
    }
  },
  components: {
    ClassroomList
        },
  async mounted(){
    const params = this.getParams(window.location.href)
    console.log(params)
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
      await this.getTabData(key);
    },
    onChange(pageNumber) {
      window.location.href = window.location.pathname + `?type=${this.tabValue}&page=${pageNumber}${this.searchValue ? `&search=${this.searchValue}` : ''}`
    },
    async getTabData(type, pageNumber=1) {
      await this.$axios.get(`/api/me/courses?title=${this.searchValue}&limit=${this.pageSize}&offset=0&page=${pageNumber}&type=${type}`).then((res) => {
        this.courseLists = res.data.data
      });
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