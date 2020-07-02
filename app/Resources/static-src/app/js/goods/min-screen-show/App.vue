<template>
  <div class="min-screen-show">
    min-screen-show
  </div>
</template>

<script>
  import axios from 'axios';
  export default {
    data() {
      return {
        componentsData: {}
      }
    },
    components: {
    },
    methods: {
      
    },
    created() {
      axios.get('/api/goods/1', {
        headers: { 'Accept': 'application/vnd.edusoho.v2+json'}
      }).then((res) => {
        this.componentsData = res.data;
      });
    }
  }
</script>
