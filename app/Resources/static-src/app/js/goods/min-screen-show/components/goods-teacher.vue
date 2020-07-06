<template>
  <div class="info-teacher">
    <template v-if="teachers.length">
      <div class="info-teacher__item clearfix" v-for="teacher in teachers" :key="teacher.id">
        <div class="pull-left teacher-img">
          <img :src="teacher.avatar.small" alt="">
        </div>
        <div class="pull-left teacher-info">
          <p class="teacher-info__name">{{ teacher.name }}</p>
          <p class="teacher-info__describe text-overflow">{{ teacher.title }}</p>
        </div>
      </div>
    </template>
    <div v-else class="info-teacher__item">
      暂无教师~
    </div>
  </div>
</template>

<script>
export default {
  props: {
    teachers: {
      type: Array,
      default: () => []
    }
  }
}
</script>