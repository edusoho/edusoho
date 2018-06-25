<template>
  <div class="course-detail__head">
    <img :src="courseSet.cover.large" alt="">
  </div>
</template>
<script>
export default {
  props: ['courseSet']
}
</script>
