<template>
  <div class="course-detail__head pos-rl">
    <div class="course-detail__head--img">
      <img :src="cover" alt="">
    </div>
    <countDown
      v-if="seckillActivities && counting && !isEmpty && seckillActivities.status === 'ongoing'"
      :activity="seckillActivities"
      @timesUp="expire"
      @sellOut="sellOut">
    </countDown>
    <tagLink :tagData="tagData"></tagLink>
  </div>
</template>
<script>
import countDown from '@/containers/components/e-marketing/e-count-down/index';
import tagLink from '@/containers/components/e-tag-link/e-tag-link';

export default {
  components: {
    countDown,
    tagLink,
  },
  data() {
    return {
      counting: true,
      isEmpty: false,
      tagData: {
        isShow: true,
        money: 666.66,
        link: 'edusoho.com',
        className: 'course-tag',
      }
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
