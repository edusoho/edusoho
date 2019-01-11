<template>
  <div class="e-groupon">
    <div class="e-coupon__title" v-if="showTitle === 'show'">{{ activityTitle }}</div>
    <div class="e-groupon__tag" v-if="tag.length">{{ tag }}</div>
    <div class="e-groupon__image-container" :class="{ 'e-groupon__image-empty': !activity.cover }">
      <img v-if="activity.cover" class="e-groupon__image" :src="activity.cover" alt="">
    </div>
    <div v-if="type === 'seckill'" :class="['seckill-countdown-container clearfix', seckillClass]">
      <span class="pull-left status-title">秒杀{{activity.status==='ongoing' && seckilling ? '中' : ''}}</span>
      <div class="pull-right text-12" v-html="statusTitle">
        <!-- <span v-if="activity.status==='unstart'">距离开抢<span class="ml10">03:29:38</span></span>
        <span v-if="activity.status==='ongoing'">距离结束仅剩<span class="ml10">03:29:38</span></span>
        <span v-else>秒杀已结束</span> -->
      </div>
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
    return {
      statusTitle: '',
      seckillClass: 'seckill-unstart',
      seckilling: false
    }
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
          if(status === 'closed') {
            this.statusTitle = '秒杀已结束';
            this.seckillClass = 'seckill-closed';
            return '已结束';
          }
          if(status === 'ongoing') {
            if (this.activity.productRemaind == 0) {
              this.statusTitle = '商品已售空';
              this.seckillClass = 'seckill-closed';
            }
            const startStamp = new Date(this.activity.startTime).getTime();
            const endStamp = new Date(this.activity.endTime).getTime();
            const nowStamp = new Date().getTime();
            const buyCountDownStamp = startStamp - nowStamp;
            if ((startStamp < nowStamp) && (nowStamp < endStamp)) {
              this.seckilling = true;
              this.seckillClass = 'seckill-ongoing';
              this.statusTitle = '距离结束仅剩<span class="ml10 mlm">03:29:38</span>';
              return '马上秒'
            };
            if (startStamp > nowStamp) {
              this.seckilling = false;
              this.seckillClass = 'seckill-unstart';
              this.statusTitle = '距离开抢<span class="ml10 mlm">03:29:38</span>';
              return '提醒我'
            };
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
