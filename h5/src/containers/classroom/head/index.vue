<template>
  <div class="course-detail__head">
    <div class="course-detail__head--img">
      <img :src="cover" alt="">
    </div>
    <countDown
      v-if="seckillActivities && counting && !isEmpty && seckillActivities.status === 'ongoing'"
      :activity="seckillActivities"
      @timesUp="expire"
      @sellOut="sellOut">
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
      counting: true,
      isEmpty: false
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
    },
    sellOut() {
      this.isEmpty = true
      this.$emit('goodsEmpty')
    }
  }
}
</script>
