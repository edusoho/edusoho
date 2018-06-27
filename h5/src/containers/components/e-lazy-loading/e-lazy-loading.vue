<template>
  <van-list
    v-model="loading"
    :finished="finished"
    @load="onLoad"
  >
    <!-- <courseItem></courseItem> -->
  </van-list>
</template>

<script>
  import courseItem from '../e-course/e-course.vue';

  export default {
    components: {
      courseItem,
    },
    data() {
      return {
        list: [],
        loading: false,
        finished: false
      };
    },

    methods: {
      onLoad() {

      }
    }
  }
</script>
