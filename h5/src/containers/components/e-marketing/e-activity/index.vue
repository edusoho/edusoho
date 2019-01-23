<template>
  <div class="e-groupon" @click="getMarketUrl(activity.status)">
    <div class="e-coupon__title" v-if="showTitle === 'show'">{{ activityTitle }}</div>
    <div class="e-groupon__image-container" :class="{ 'e-groupon__image-empty': !activity.cover }">
      <img v-if="activity.cover" class="e-groupon__image" :src="activity.cover" alt="">
      <div class="e-groupon__tag" v-if="tag.length">{{ tag }}</div>
    </div>
    <countDown
      v-if="type === 'seckill' && counting && isEmpty"
      :activity="activity"
      @timesUp="expire"
      @sellOut="sellOut">
    </countDown>
    <div class="e-groupon__context">
      <div class="context-title text-overflow">{{ activity.name || '活动名称' }}</div>
      <div class="context-sale clearfix">
        <div class="type-tag" v-if="type !== 'groupon'">{{ type === 'cut' ? '砍价享' : '秒杀价' }}</div>
        <div class="context-sale__sale-price">￥{{ activityPrice }}</div>
        <div v-if="activity.originPrice" class="context-sale__origin-price">原价￥{{ activity.originPrice }}</div>
        <a v-if="isEmpty" class="context-sale__shopping" :class="[activity.status, {'bg-grey': !isEmpty}]" href="javascript:;">
          {{ grouponStatus }}
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import countDown from '../e-count-down/index';

export default {
  components: {
    countDown
  },
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
    },
    feedback: {
      type: Boolean,
      default: true
    }
  },
  data () {
    return {
      counting: true,
    }
  },
  computed: {
    isEmpty: {
      get() {
        return !!(Object.values(this.activity).length);
      },
      set(val) {
        console.log(val,'val')
        return val;
      }
    },
    activityId() {
      return Number(this.activity.id);
    },
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
    },
    grouponStatus() {
      const status = this.activity.status;
      if (status === 'ongoing' && !this.counting) {
        this.activity.status = 'closed';
        return '已结束';
      };
      switch (this.type) {
        case 'groupon':
          if(status === 'unstart') return '活动未开始';
          if(status === 'ongoing') return '去拼团';
          if(status === 'closed') return '活动已结束';
          break;
        case 'seckill':
          if(status === 'unstart') return '秒杀未开始';
          if(status === 'closed') return '已结束';
          if(status === 'ongoing') {
            if (this.activity.productRemaind == 0) return '已售空';
            const nowStamp = new Date().getTime();
            const startStamp = new Date(this.activity.startTime).getTime();
            const endStamp = new Date(this.activity.endTime).getTime();
            if ((startStamp < nowStamp) && (nowStamp < endStamp)) return '马上秒'
            if (startStamp > nowStamp) return '提醒我'
          }
          break;
        case 'cut':
          if(status === 'unstart') return '砍价未开始';
          if(status === 'ongoing') return '发起砍价';
          if(status === 'closed') return '砍价已结束';
          break;
      }
    },
  },
  methods: {
    getMarketUrl(status) {
      if (!this.feedback) return;
      this.$emit('activityHandle', this.activityId)
    },
    expire() {
      this.counting = false;
    },
    sellOut() {
      this.isEmpty = true;
    }
  }
}
</script>
