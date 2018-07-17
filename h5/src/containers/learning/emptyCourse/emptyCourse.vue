<template>
  <div class="empty-course">
    <img class="empty-course__img" src="/static/images/courseEmpty.png" alt="">
    <p class="empty-course__text">暂无课程</p>
    <div class="empty-course__btn" @click="jumpBack">+ 更多好课等您加入</div>
  </div>
</template>

<script>
  import store from '@/store';

  export default {
    methods: {
      jumpBack() {
        this.$router.push({
          name: 'find',
          query: {
            redirect: 'find'
          }
        });
      }
    },
  }

</script>
