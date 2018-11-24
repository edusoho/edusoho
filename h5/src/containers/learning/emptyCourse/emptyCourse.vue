<template>
  <div class="empty-course">
    <img class="empty-course__img" src="static/images/courseEmpty.png" alt="">
    <p class="empty-course__text">暂无{{typeText}}</p>
    <div v-if="hasButton" class="empty-course__btn" @click="jumpBack">+ 更多{{moreText}}等您加入</div>
  </div>
</template>

<script>
  import store from '@/store';

  export default {
    props: {
      hasButton: {
        type: Boolean,
        default: true
      },
      type: {
        type: String,
        default: 'course'
      }
    },
    data() {
      return {
        typeText: this.type === 'course' ? '课程' : '班级',
        moreText: this.type === 'course' ? '好课' : '班级'
      }
    },
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
