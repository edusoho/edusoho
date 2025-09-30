<template>
  <div>
    <ClassroomItem v-for="(item, index) in classroomLists" :key="index" :classItem="item" :tabValue="tabValue"></ClassroomItem>
        <div v-if="classroomLists.length == 0" class="searchEmptyCourse">
          <img class="searchEmptyCourseImg" src="/static-dist/app/img/vue/goods/empty-course.png" alt="">
          <p class="searchEmptyCourseContent">{{ 'tab.classroom_tab.classroom_list.empty'|trans }}</p>
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
    },
    tabValue: {
      type: String,
      default: ''
    }
  },
  components: {
    ClassroomItem
  }
}

</script>
