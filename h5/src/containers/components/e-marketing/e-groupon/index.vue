<template>
  <div class="e-groupon">
    <div class="e-coupon__title" v-if="showTitle === 'show'">{{ activityTitle }}</div>
    <div class="e-groupon__tag" v-if="tag.length">{{ tag }}</div>
    <div class="e-groupon__image-container" :class="{ 'e-groupon__image-empty': !activity.cover }">
      <img v-if="activity.cover" class="e-groupon__image" :src="activity.cover" alt="">
    </div>
    <div v-if="type === 'seckill'" class="seckill-countdown-container clearfix seckill-unstart">
      <span class="pull-left status-title">秒杀中</span>
      <span class="pull-right text-12">距离结束仅剩<span class="ml10">03:29:38</span></span>
    </div>
    <div class="e-groupon__context">
      <div class="context-title text-overflow">{{ activity.name || '拼团活动' }}</div>
      <div class="context-sale">
        <div class="type-tag" v-if="type !== 'groupon'">{{ type === 'cut' ? '砍价享' : '秒杀价' }}</div>
        <div class="context-sale__sale-price">￥{{ activityPrice }}</div>
        <div v-if="activity.originPrice" class="context-sale__origin-price">原价{{ activity.originPrice }} 元</div>
        <a class="context-sale__shopping" :class="activity.status" href="javascript:;">
          {{ grouponStatus(activity.status)}}
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import { formatTime } from '@/utils/date-toolkit';

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
    return {}
  },
  computed: {
    activityTitle() {
      if (this.type === 'seckill') return '秒杀';
      if (this.type === 'cut') return '砍价';
      return '拼团';
    },
    activityPrice() {
      if (!Object.values(this.activity).length) return '00.00';
      if (this.type === 'seckill') return this.activity.rule.seckillPrice;
      if (this.type === 'cut') return this.activity.rule.lowestPrice;
      if (this.type === 'groupon') return this.activity.rule.memberPrice;
    }
  },
  methods: {
    grouponStatus(status) {
      switch (this.type) {
        case 'groupon':
          if(status === 'unstart') return '活动未开始';
          if(status === 'ongoing') return '去拼团';
          if(status === 'closed') return '活动已结束';
        case 'seckill':
          if(status === 'unstart') return '秒杀未开始';
          if(status === 'closed') return '秒杀已结束';
          if(status === 'ongoing') {
            if (this.activity.productRemaind == 0) {}
            const startStamp = new Date(this.activity.startTime).getTime();
            const endStamp = new Date(this.activity.endTime).getTime();
            const nowStamp = new Date().getTime();
            if ((startStamp < nowStamp) && (nowStamp < endStamp)) return '秒杀中';
            if (startStamp > nowStamp) return '';
          }
        case 'cut':
          if(status === 'unstart') return '砍价未开始';
          if(status === 'ongoing') return '发起砍价';
          if(status === 'closed') return '砍价已结束';
      }
    }
  }
}
</script>
