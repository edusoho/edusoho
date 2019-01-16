<template>
  <div class="e-groupon">
    <div class="e-coupon__title" v-if="showTitle === 'show'">{{ activityTitle }}</div>
    <div class="e-groupon__tag" v-if="tag.length">{{ tag }}</div>
    <div class="e-groupon__image-container" :class="{ 'e-groupon__image-empty': !activity.cover }">
      <img v-if="activity.cover" class="e-groupon__image" :src="activity.cover" alt="">
    </div>
    <div v-if="type === 'seckill'" :class="['seckill-countdown-container clearfix', seckillClass]">
      <span class="pull-left status-title">秒杀{{activity.status==='ongoing' && seckilling ? '中' : ''}}</span>
      <div class="pull-right text-12" v-html="statusTitle"></div>
    </div>
    <div class="e-groupon__context">
      <div class="context-title text-overflow">{{ activity.name || '拼团活动' }}</div>
      <div class="context-sale clearfix">
        <div class="type-tag" v-if="type !== 'groupon'">{{ type === 'cut' ? '砍价享' : '秒杀价' }}</div>
        <div class="context-sale__sale-price">￥{{ activityPrice }}</div>
        <div v-if="activity.originPrice" class="context-sale__origin-price">原价{{ activity.originPrice }} 元</div>
        <a class="context-sale__shopping" :class="activity.status" href="javascript:;" @click="getMarketUrl(activity.status)">
          {{ grouponStatus(activity.status)}}
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import { dateTimeDown } from '@/utils/date-toolkit';
import Api from '@/api';

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
      seckilling: false,
      buyCountDownText: '',
      endCountDownText: '',
      activityId: Number(this.activity.id),
      endStamp: new Date(this.activity.endTime).getTime(),
      startStamp: new Date(this.activity.startTime).getTime()
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
  created() {
    this.countDown();
  },
  beforeDestroy() {
    clearInterval(this.timer);
    this.timer = null;
  },
  methods: {
    grouponStatus(status) {
      switch (this.type) {
        case 'groupon':
          if(status === 'unstart') return '活动未开始';
          if(status === 'ongoing') return '去拼团';
          if(status === 'closed') return '活动已结束';
        case 'seckill':
          if(status === 'unstart') {
            this.statusTitle = '秒杀未开始';
            return '秒杀未开始';
          }
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
            const nowStamp = new Date().getTime();
            if ((this.startStamp < nowStamp) && (nowStamp < this.endStamp)) {
              this.seckilling = true;
              this.seckillClass = 'seckill-ongoing';
              this.statusTitle = `距离结束仅剩<span class="ml10 mlm">${this.endCountDownText}</span>`;
              return '马上秒'
            };
            if (this.startStamp > nowStamp) {
              this.seckilling = false;
              this.seckillClass = 'seckill-unstart';
              this.statusTitle = `距离开抢<span class="ml10 mlm">${this.buyCountDownText}</span>`;
              return '提醒我'
            };
          }
        case 'cut':
          if(status === 'unstart') return '砍价未开始';
          if(status === 'ongoing') return '发起砍价';
          if(status === 'closed') return '砍价已结束';
      }
    },
    countDown() {
      const timer = setInterval(() => {
        this.endCountDownText = dateTimeDown(this.endStamp);
        this.buyCountDownText = dateTimeDown(this.startStamp);
      }, 1000);
    },
    getMarketUrl(status) {
      if (status === 'ongoing' && this.seckilling) {
        const params = {
          domainUri: 'http://lvliujie.st.edusoho.cn',
          itemUri: '',
          source: 'h5'
        }
        Api.marketingActivities({
          query: {
            activityId: this.activityId
          },
          data: params
        }).then((res) => {
          window.location.href = res.url;
        }).catch((err) => {
          console.log(err.message)
        })
      }
    }
  }
}
</script>
