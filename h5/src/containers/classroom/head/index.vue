<template>
  <div>
    <div class="course-detail__head">
      <div class="course-detail__head--img">
        <img :src="cover" alt="">
      </div>
    </div>
    <countDown v-if="seckillActivities" :activity="seckillActivities"></countDown>
  </div>
</template>
<script>
import countDown from '@/containers/components/e-marketing/e-count-down/index';

export default {
  components: {
    countDown
  },
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
