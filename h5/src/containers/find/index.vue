<template>
  <div class="find-page">
    <e-course-list></e-course-list>
  </div>
</template>

<script>
  import courseList from '../components/e-course-list/e-course-list.vue';

  export default {
    data () {
      return {
      };
    },
    components: {
      'e-course-list': courseList,
    },
  }
</script>
