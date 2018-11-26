<template>
  <div class="course-detail__head">
    <div class="course-detail__head--img">
      <img :src="cover" alt="">
    </div>
  </div>
</template>
<script>

export default {
  data() {
    return {
    };
  },
  props: {
    cover: {
      type: String,
      default: '',
    },
  },
}
</script>
