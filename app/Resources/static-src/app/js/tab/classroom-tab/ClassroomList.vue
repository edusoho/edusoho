<template>
  <div>
    <ClassroomItem v-for="(item, index) in classroomLists" :key="index" :classItem="item"></ClassroomItem>
        <div v-if="classroomLists.length == 0" class="searchEmptyCourse">
          <img class="searchEmptyCourseImg" src="/static-dist/app/img/vue/goods/empty-course.png" alt="">
          <p class="searchEmptyCourseContent">暂无班级</p>
        </div>
  </div>
</template>
<script>
import ClassroomItem from './ClassroomItem.vue';

export default {
  props: {
    classroomLists: {
      type: Array,
      default: []
    }
  },
  components: {
    ClassroomItem
  }
}

</script>