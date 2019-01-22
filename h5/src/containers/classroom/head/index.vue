<template>
  <div class="course-detail__head">
    <div class="course-detail__head--img">
      <img :src="cover" alt="">
    </div>
    <countDown
      v-if="seckillActivities.type === 'seckill' && counting"
      :activity="seckillActivities"
      @timesUp="expire">
    </countDown>
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
      counting: true
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
  methods: {
    expire() {
      this.counting = false;
    }
  }
}
</script>
