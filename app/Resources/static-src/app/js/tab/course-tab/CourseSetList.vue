<template>
  <div>
    <CourseSetItem v-for="(item, index) in courseSets" :key="index" :courseSet="item" />
    <div v-if="courseSets.length == 0" class="searchEmptyCourse">
      <img class="searchEmptyCourseImg" src="/static-dist/app/img/vue/goods/empty-course.png" alt="">
      <p class="searchEmptyCourseContent">暂无课程</p>
    </div>
  </div>
</template>
<script>
import CourseSetItem from './CourseSetItem.vue';
export default {
  props: {
    courseSets: {
      type: Array,
      default: () => []
    }
  },
  components: {
    CourseSetItem
  }
}
</script>
