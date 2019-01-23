<template>
  <div class="course-detail__head">
    <div class="course-detail__head--img">
      <img :src="cover" alt="">
    </div>
    <countDown
      v-if="seckillData && counting && !isEmpty"
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
  computed: {
    seckillData() {
      if (!this.seckillActivities) return false;
      return !!(Object.values(this.seckillActivities).length);
    },
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
