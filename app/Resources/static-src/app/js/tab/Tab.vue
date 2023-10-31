<template>
  <div>
    <div class="panel-heading" style="border-bottom: 1px solid #f5f5f5; padding: 10px 0; line-height: 30px;">
      <label class="text-18">我的课程</label>
      <div class="pull-right">
        <form class="search-form" style="margin-right: 54px;">
          <input class="search-input-content inline-block" v-model:value="searchValue" @keyup.enter="getTabData()" type="text" name="title" placeholder="请输入课程名称" />
          <a class="btn inline-block searchCourseBtn es-icon es-icon-search" type="submit" @click="getTabData()"></a>
        </form>
        <a class="live-course-btn">直播课表</a>
      </div>
    </div>

    <div class="panel-body" style="padding: 0">
    <a-tabs 
    :default-active-key="type" 
    :tabBarGutter="0"
    size="small" 
    @change="callback">
      <a-tab-pane key="learning" tab="学习中">
        <CourseList v-for="(item, index) in courseList" :key="index" :course="item"></CourseList>
      </a-tab-pane>
      <a-tab-pane key="learned" tab="已学完" force-render>
        已学完
      </a-tab-pane>
      <a-tab-pane key="expired" tab="已过期">
        已过期
      </a-tab-pane>
      <a-tab-pane key="favorited" tab="收藏">
        收藏
      </a-tab-pane>
    </a-tabs>
  </div>
  </div>
</template>
<script>
import CourseList from './CourseList.vue';
export default {
  data(){
    return {
      type: 'learning',
      searchValue: '',
      courseList: [{}]
    }
  },
  components: {
    CourseList
        },
  mounted(){
    this.getTabData();
  },
  methods: {
    callback(key) {
      console.log(key);
    },
    getTabData() {
      this.$axios.get(`/api/me/courses?title=${this.searchValue}&limit=12&offset=0`).then((res) => {
        this.courseList = res.data.data
        console.log(this.courseList)
      });
    },
  },
}
</script>
<style scoped>
</style>