<template>
  <div class="course-detail__head">
    <div class="course-detail__head--img">
      <img :src="cover" alt="">
      <div v-if="seckillActivities">秒杀倒计时 xxxx</div>
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
    seckillActivities: {
      type: Object,
      default: null,
    }
  },
}
</script>
