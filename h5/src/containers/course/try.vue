<template>
  <div class="try">
    <detail-head
      :courseSet="courseSet"></detail-head>

    <directory
      :hiddeTitle=true
      class="join-after-dirctory"
      ></directory>
  </div>
</template>
<script>
import Directory from './detail/directory';
import DetailHead from './detail/head';

export default {
  data() {
    return {
      courseSet: {}
    }
  },
  components: {
    Directory,
    DetailHead
  },
  created(){

  }
}
</script>
