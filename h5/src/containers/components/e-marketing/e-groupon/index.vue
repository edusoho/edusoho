<template>
  <div class="e-groupon">
    <div class="e-coupon__title" v-if="showTitle === 'show'">{{ activityTitle }}</div>
    <div class="e-groupon__tag" v-if="tag.length">{{ tag }}</div>
    <div class="e-groupon__image-container" :class="{ 'e-groupon__image-empty': !activity.cover }">
      <img v-if="activity.cover" class="e-groupon__image" :src="activity.cover" alt="">
    </div>
    <div class="e-groupon__context">
      <div class="context-title text-overflow">{{ activity.name || '拼团活动' }}</div>
      <div class="context-sale">
        <div class="type-tag" v-if="type === 'cut'">砍价享</div>
        <div class="context-sale__sale-price">￥{{ activity.memberPrice || '00.00' }}</div>
        <div v-if="activity.originPrice" class="context-sale__origin-price">原价{{ activity.originPrice }} 元</div>
        <a class="context-sale__shopping" :class="activity.status" href="javascript:;">{{ grounponStatus[activity.status] }}</a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'e-groupon',
  props: {
    activity: {
      type: Object,
      default: {}
    },
    tag: {
      type: String,
      default: '',
    },
    showTitle: {
      type: String,
      default: 'show'
    },
    type: {
      type: String,
      default: 'groupon'
    }
  },
  data () {
    return {
      grounponStatus: {
        unstart: '活动未开始',
        ongoing: '去拼团',
        closed: '活动已结束'
      }
    }
  },
  computed: {
    activityTitle() {
      if (this.type === 'seckill') return '秒杀';
      if (this.type === 'cut') return '砍价';
      return '拼团';
    }
  }
}
</script>
