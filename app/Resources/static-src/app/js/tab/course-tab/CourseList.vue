<template>
  <div>
    <CourseItem v-for="(item, index) in courseLists" :key="index" :course="item" :tabValue="tabValue"></CourseItem>
        <div v-if="courseLists.length == 0" class="searchEmptyCourse">
          <img class="searchEmptyCourseImg" src="/static-dist/app/img/vue/goods/empty-course.png" alt="">
          <p class="searchEmptyCourseContent">{{ 'tab.course_tab.course_list.empty'|trans }}</p>
        </div>
  </div>
</template>
<script>
import CourseItem from './CourseItem.vue';

export default {
  props:{
    courseLists: {
      type:Array,
      default:[]
    },
    tabValue: {
      type: String,
      default: ''
    }
  },
  components: {
    CourseItem
        },
}
</script>
