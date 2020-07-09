<template>
  <div v-if="teachers.length" class="info-right-teacher info-right-box">
    <div class="title">授课老师</div>
    <div class="teacher-info clearfix" v-for="item in teachers" :key="item.id">
      <img class="teacher-info__img pull-left" :src="item.avatar.large" alt="">
      <div class="teacher-info__introduce introduce pull-left">
        <p class="introduce__name">{{ item.name }}</p>
        <p class="introduce__text">{{ item.title }}</p>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      teachers: {
        type: Array,
        default: function () {
          return []
        }
      }
    }
  }
</script>