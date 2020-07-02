<template>
  <div class="goods">
    <detail :details="details" />
    <course-info :details="details" />
  </div>
</template>

<script>
  import Detail from './detail';
  import CourseInfo from './course-info';
  import axios from 'axios';
  export default {
    data() {
      return {
        details: {}
      }
    },
    components: {
      Detail,
      CourseInfo
    },
    methods: {
      
    },
    created() {
      axios.get('/api/goods/1', {
        headers: { 'Accept': 'application/vnd.edusoho.v2+json'}
      }).then((res) => {
        this.details = res.data;
      });
    }
  }
</script>
